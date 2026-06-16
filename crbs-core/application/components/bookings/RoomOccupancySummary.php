<?php

namespace app\components\bookings;

defined('BASEPATH') OR exit('No direct script access allowed');


class RoomOccupancySummary
{
	const STATE_ACTIVE = 'active';
	const STATE_UPCOMING = 'upcoming';
	const STATE_FREE = 'free';


	public static function forRoom(iterable $slots, object $room, \DateTimeInterface $now): array
	{
		$room_id = $room->room_id ?? null;
		$booked = [];
		$current = null;
		$current_has_slot = false;
		$now_date = $now->format('Y-m-d');
		$now_time = $now->format('H:i:s');

		foreach ($slots as $slot) {
			if (!self::slotBelongsToRoom($slot, $room_id)) {
				continue;
			}

			$date = self::slotDate($slot);
			$period = $slot->period ?? null;
			$start = self::periodTime($period, 'time_start');
			$end = self::periodTime($period, 'time_end');

			if (!$date || !$start || !$end) {
				continue;
			}

			$is_current_period = ($date === $now_date && $start <= $now_time && $now_time < $end);
			if ($is_current_period) {
				$current_has_slot = true;
			}

			if (($slot->status ?? null) !== Slot::STATUS_BOOKED) {
				continue;
			}

			$item = self::buildItem($slot, $date, $start, $end);
			$booked[] = $item;

			if ($is_current_period) {
				$current = $item;
			}
		}

		usort($booked, function ($a, $b) {
			return [$a['date'], $a['time_start']] <=> [$b['date'], $b['time_start']];
		});

		$next = null;
		foreach ($booked as $item) {
			if ($item['date'] > $now_date || ($item['date'] === $now_date && $item['time_start'] > $now_time)) {
				$next = $item;
				break;
			}
		}

		$state = self::STATE_FREE;
		if ($current) {
			$state = self::STATE_ACTIVE;
		} elseif ($next) {
			$state = self::STATE_UPCOMING;
		}

		return [
			'state' => $state,
			'current' => $current,
			'next' => $next,
			'has_current_slot' => $current_has_slot,
		];
	}


	private static function slotBelongsToRoom(object $slot, $room_id): bool
	{
		$slot_room_id = $slot->room->room_id ?? $slot->booking->room_id ?? null;
		return (string) $slot_room_id === (string) $room_id;
	}


	private static function slotDate(object $slot): ?string
	{
		return $slot->date->date ?? $slot->booking->date ?? null;
	}


	private static function periodTime(?object $period, string $field): ?string
	{
		if (!$period || empty($period->{$field})) {
			return null;
		}

		return date('H:i:s', strtotime($period->{$field}));
	}


	private static function buildItem(object $slot, string $date, string $start, string $end): array
	{
		$booking = $slot->booking ?? null;
		$user = $booking->user ?? null;
		$show_user = self::viewDataValue($slot, 'show_user', true);
		$user_label = 'Reservado';

		if ($show_user && $user) {
			$user_label = !empty($user->displayname)
				? $user->displayname
				: ($user->username ?? $user_label);
		}

		return [
			'date' => $date,
			'time_start' => $start,
			'time_end' => $end,
			'time_label' => self::formatTime($start) . ' - ' . self::formatTime($end),
			'period_label' => $slot->period->name ?? '',
			'user_label' => $user_label,
		];
	}


	private static function viewDataValue(object $slot, string $key, $default = null)
	{
		$view_data = $slot->view_data ?? [];

		if (is_array($view_data) && array_key_exists($key, $view_data)) {
			return $view_data[$key];
		}

		if (is_object($view_data) && isset($view_data->{$key})) {
			return $view_data->{$key};
		}

		return $default;
	}


	private static function formatTime(string $time): string
	{
		return date('H:i', strtotime($time));
	}

}
