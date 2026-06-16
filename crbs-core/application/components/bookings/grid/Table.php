<?php

namespace app\components\bookings\grid;

defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\Calendar;
use app\components\bookings\Context;
use app\components\bookings\RoomOccupancySummary;
use app\components\bookings\Slot;


class Table
{


	// CI instance
	private $CI;


	// Context instance
	private $context;

	// Column width
	private $col_width = FALSE;


	public function __construct(Context $context)
	{
		$this->CI =& get_instance();
		$this->context = $context;

		// Determine width of columns based on number of items
		if ( ! $this->context->exception) {

			$col_count = match ($this->context->columns) {
				'periods' => is_array($this->context->periods) ? count($this->context->periods) + 1 : 1,
				'days' => is_array($this->context->dates) ? count($this->context->dates) + 1 : 1,
				'rooms' => is_array($this->context->rooms) ? count($this->context->rooms) + 1 : 1,
				default => 0,
			};

			$this->col_width = ($col_count > 0)
				? sprintf('%d%%', round(100 / $col_count))
				: '5%';

		}
	}


	public function get_columns()
	{
		$cols = [
			'periods' => ['name' => 'period', 'items' => $this->context->periods],
			'days' => ['name' => 'date', 'items' => $this->context->dates],
			'rooms' => ['name' => 'room', 'items' => $this->context->rooms],
		];

		return array_key_exists($this->context->columns, $cols)
			? $cols[$this->context->columns]
			: [];
	}


	public function get_rows()
	{
		$rows = [
			'periods' => ['name' => 'period', 'items' => $this->context->periods],
			'days' => ['name' => 'date', 'items' => $this->context->dates],
			'rooms' => ['name' => 'room', 'items' => $this->context->rooms],
		];

		return array_key_exists($this->context->rows, $rows)
			? $rows[$this->context->rows]
			: [];
	}


	/**
	 * Render the bookings as a list of room cards with slot items inside.
	 *
	 */
	public function render()
	{
		$row_config = $this->get_rows();
		$column_config = $this->get_columns();
		$day_names = Calendar::get_day_names();
		$room_summaries = $this->build_room_summaries();
		$next_period_panel = $this->render_next_period_panel($room_summaries);

		$cards_html = '';

		if (is_array($row_config['items'])) {

			foreach ($row_config['items'] as $row_idx => $row_item) {

				// Build the card header based on the row type
				$room_summary = ($row_config['name'] === 'room')
					? ($room_summaries[$row_item->room_id] ?? null)
					: null;
				$header_html = $this->render_card_header($row_config['name'], $row_item, $day_names, $room_summary);

				// Build slot items for this row
				$slots_html = '';

				if (is_array($column_config['items'])) {
					foreach ($column_config['items'] as $col_item) {

						// Column label (period name + time, date, or room name)
						$col_label = $this->get_column_label($column_config['name'], $col_item, $day_names);

						$cell_data = [
							'row' => ['name' => $row_config['name'], $row_config['name'] => $row_item],
							'column' => ['name' => $column_config['name'], $column_config['name'] => $col_item],
						];

						$slot_placeholder = $this->render_cell($cell_data);

						// Wrap slot with column label
						$slots_html .= "<div class='cps-slot-wrapper'>"
							. "<div class='cps-slot-label'>{$col_label}</div>"
							. "<div class='cps-slot-content'>{$slot_placeholder}</div>"
							. "</div>";
					}
				}

				// Build the card with native disclosure so it works without extra JS plugins.
				$is_first = ($row_idx === 0);
				$open_attr = $is_first ? ' open' : '';
				$card_classes = ['cps-room-card'];
				$card_attrs = '';
				if ($row_config['name'] === 'room') {
					$status = $room_summary['status'] ?? 'free';
					$card_classes[] = "cps-room-card-status-{$status}";
					$card_classes[] = $this->get_floor_accent_class($row_item);
					$card_attrs = sprintf(" id='room-card-%d'", (int) $row_item->room_id);
				}
				$card_class_str = implode(' ', array_filter($card_classes));

				$cards_html .= "<details class='{$card_class_str}'{$card_attrs}{$open_attr}>"
					. "<summary class='cps-room-card-header'>"
					. $header_html
					. "<span class='cps-room-card-toggle' aria-hidden='true'>&#9660;</span>"
					. "</summary>"
					. "<div class='cps-room-card-body'>"
					. $slots_html
					. "</div>"
					. "</details>";
			}
		}

		// Parse slots (replace placeholders with actual slot views)
		$cards_html = $this->parse_slots($cards_html);

		$classes = 'bookings-grid-cards';
		if (setting('grid_highlight') == 1) {
			$classes .= ' has-highlight';
		}

		$cards_panel = "<section class='cps-bookings-view-panel' data-bookings-panel='rooms'>"
			. "<div class='{$classes}'>{$cards_html}</div>"
			. "</section>";

		if ($this->context->display_type !== 'day') {
			return $cards_panel;
		}

		if ($next_period_panel === '') {
			$next_period_label = html_escape(lang('booking.next_period.with_bookings'));
			$next_period_title = html_escape(lang('booking.view.next_period'));
			$next_period_none = html_escape(lang('booking.next_period.none'));
			$next_period_panel = "<section class='cps-next-period-panel' aria-label='{$next_period_label}'>"
				. "<div class='cps-next-period-header'>"
				. "<div><span class='cps-next-period-kicker'>{$next_period_title}</span><strong>{$next_period_none}</strong></div>"
				. "</div>"
				. "</section>";
		}

		$schedule_label = html_escape(lang('booking.view.schedule'));
		$rooms_label = html_escape(lang('booking.view.rooms'));
		$next_period_label = html_escape(lang('booking.view.next_period'));
		$tabs = "<div class='cps-bookings-view-tabs' role='tablist' aria-label='{$schedule_label}'>"
			. "<button type='button' class='cps-bookings-view-tab is-active' data-bookings-tab='rooms' aria-selected='true'>{$rooms_label}</button>"
			. "<button type='button' class='cps-bookings-view-tab' data-bookings-tab='next-period' aria-selected='false'>{$next_period_label}</button>"
			. "</div>";

		$next_period_panel = "<section class='cps-bookings-view-panel' data-bookings-panel='next-period' hidden>{$next_period_panel}</section>";

		return "<div class='cps-bookings-view-switcher'>{$tabs}{$cards_panel}{$next_period_panel}</div>";
	}


	/**
	 * Build the card header HTML based on row type.
	 */
	private function render_card_header($name, $item, $day_names, ?array $room_summary = null)
	{
		switch ($name) {
			case 'room':
				$room_name = html_escape($item->name);
				$owner = '';
				if ($item->owner) {
					$owner_name = $item->owner->displayname ?: $item->owner->username;
					$owner = "<span class='cps-room-card-owner text-xs opacity-80 font-normal'>" . html_escape($owner_name) . "</span>";
				}
				$info_icon = '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01"/></svg>';
				$info_label = html_escape(lang('room.room_details'));
				$info_link = anchor("rooms/info/{$item->room_id}", $info_icon . "<span class=\"cps-sr-only\">{$info_label}</span>", [
					'class'        => 'cps-room-info-button',
					'up-layer'     => 'new modal',
					'up-target'    => '.room-info',
					'up-history'   => 'false',
					'up-size'      => 'large',
					'up-preload'   => '',
					'onclick'      => 'event.stopPropagation();',
					'up-tooltip'   => lang('room.room_details'),
				]);
				$icon_room = '<svg class="w-4 h-4 text-cps-red flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
				$group_name = $this->get_room_group_name($item);
				$group_badge = $group_name
					? "<span class='cps-floor-chip'>" . html_escape($group_name) . "</span>"
					: '';
				$status_badge = $this->render_room_status_badge($room_summary);
				$schedule_summary = $this->render_room_schedule_summary($room_summary);

				return "<div class='cps-room-card-heading'>"
					. "<div class='cps-room-card-title-row'>"
					. "<div class='flex flex-wrap items-center gap-2'>{$icon_room}<strong class='cps-room-card-name'>{$room_name}</strong>{$owner}{$group_badge}{$status_badge}</div>"
					. $info_link
					. "</div>"
					. $schedule_summary
					. "</div>";

			case 'period':
				$period_name = html_escape($item->name);
				$time_start = date_output_time($item->time_start);
				$time_end = date_output_time($item->time_end);
				$icon_clock = '<svg class="w-4 h-4 text-cps-red flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>';
				return "<div class='flex items-center gap-2'>{$icon_clock}"
					. "<strong>{$period_name}</strong>"
					. "<span class='text-xs text-cps-gray-text'>{$time_start} - {$time_end}</span></div>";

			case 'date':
				$date_str = date_output($item->date);
				$weekday = $item->weekday;
				$day_name = $day_names["{$weekday}"] ?? '';
				$is_today = ($item->date === $this->context->today->format('Y-m-d'));
				$today_class = $is_today ? 'text-cps-red font-bold' : '';
				$icon_cal = '<svg class="w-4 h-4 text-cps-red flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
				return "<div class='flex items-center gap-2'>{$icon_cal}"
					. "<strong class='{$today_class}'>{$day_name} - {$date_str}</strong></div>";

			default:
				return '';
		}
	}


	/**
	 * Get the label for a column item.
	 */
	private function get_column_label($name, $item, $day_names)
	{
		switch ($name) {
			case 'period':
				$pname = html_escape($item->name);
				$time_start = date_output_time($item->time_start);
				$time_end = date_output_time($item->time_end);
				return "<strong>{$pname}</strong> <span class='text-xs text-cps-gray-text'>({$time_start} - {$time_end})</span>";

			case 'room':
				$rname = html_escape($item->name);
				return "<strong>{$rname}</strong>";

			case 'date':
				$date_str = date_output($item->date);
				$weekday = $item->weekday;
				$day_name = $day_names["{$weekday}"] ?? '';
				return "<strong>{$day_name}</strong> <span class='text-xs text-cps-gray-text'>{$date_str}</span>";

			default:
				return '';
		}
	}


	private function render_cell($params)
	{
		$data = [];

		switch ($this->context->display_type) {
			case 'day': $data['date'] = $this->context->date_info; break;
			case 'room': $data['room'] = $this->context->room; break;
		}

		$row_key = $params['row']['name'];
		$column_key = $params['column']['name'];

		$data[$row_key] = $params['row'][$row_key];
		$data[$column_key] = $params['column'][$column_key];

		extract($data);

		$slot_key = Slot::generate_key($date->date, $period->period_id, $room->room_id);

		return '{' . $slot_key . '}';
	}


	private function build_room_summaries()
	{
		if ($this->context->display_type !== 'day' || ! is_array($this->context->rooms) || ! is_array($this->context->slots)) {
			return [];
		}

		$now = $this->current_datetime();

		$summaries = [];
		foreach ($this->context->rooms as $room) {
			$summary = RoomOccupancySummary::forRoom($this->context->slots, $room, $now);
			$summaries[$room->room_id] = [
				'room' => $room,
				'status' => $summary['state'],
				'current' => $summary['current'],
				'next' => $summary['next'],
			];
		}

		return $summaries;
	}


	private function render_next_period_panel(array $room_summaries)
	{
		if ($this->context->display_type !== 'day' || empty($room_summaries) || ! is_array($this->context->periods)) {
			return '';
		}

		$selected_date = $this->context->date_info ? $this->context->date_info->date : null;
		$today = $this->context->today ? $this->context->today->format('Y-m-d') : date('Y-m-d');
		$is_today = ($selected_date === $today);
		$is_future = ($selected_date && $selected_date > $today);
		$now_time = $this->current_datetime()->format('H:i:s');

		$target_period = null;
		foreach ($this->context->periods as $period) {
			$has_bookings = false;
			foreach ($this->context->slots as $slot) {
				if ($slot->booking && $slot->period->period_id == $period->period_id) {
					$has_bookings = true;
					break;
				}
			}
			if ( ! $has_bookings) {
				continue;
			}
			if ($is_today && $period->time_start <= $now_time) {
				continue;
			}
			if ( ! $is_today && ! $is_future && $period->time_start <= $now_time) {
				continue;
			}
			$target_period = $period;
			break;
		}

		if ( ! $target_period) {
			return '';
		}

		$groups = [];
		foreach ($this->context->slots as $slot) {
			if ( ! $slot->booking || $slot->period->period_id != $target_period->period_id) {
				continue;
			}
			$group = $this->get_room_group_name($slot->room) ?: lang('booking.room.group.none');
			$groups[$group][] = $slot->room;
		}

		if (empty($groups)) {
			return '';
		}

		$group_html = '';
		foreach ($groups as $group_name => $rooms) {
			$room_links = [];
			foreach ($rooms as $room) {
				$room_id = (int) $room->room_id;
				$room_links[] = "<a href='#room-card-{$room_id}' class='cps-next-period-room'>" . html_escape($room->name) . "</a>";
			}
			$group_html .= "<div class='cps-next-period-group'>"
				. "<div class='cps-next-period-group-name'>" . html_escape($group_name) . "</div>"
				. "<div class='cps-next-period-rooms'>" . implode('', $room_links) . "</div>"
				. "</div>";
		}

		$period_name = html_escape($target_period->name);
		$period_time = date_output_time($target_period->time_start) . ' - ' . date_output_time($target_period->time_end);

		$next_period_label = html_escape(lang('booking.next_period.with_bookings'));
		$next_period_title = html_escape(lang('booking.view.next_period'));

		return "<section class='cps-next-period-panel' aria-label='{$next_period_label}'>"
			. "<div class='cps-next-period-header'>"
			. "<div><span class='cps-next-period-kicker'>{$next_period_title}</span><strong>{$period_name}</strong></div>"
			. "<span class='cps-next-period-time'>{$period_time}</span>"
			. "</div>"
			. "<div class='cps-next-period-content'>{$group_html}</div>"
			. "</section>";
	}


	private function render_room_status_badge(?array $summary)
	{
		$status = $summary['status'] ?? 'free';
		$labels = [
			'free' => lang('booking.room.status.free'),
			'active' => lang('booking.room.status.active'),
			'upcoming' => lang('booking.room.status.upcoming'),
		];
		$label = $labels[$status] ?? $labels['free'];
		return "<span class='cps-room-status-badge cps-room-status-badge-{$status}'>" . html_escape($label) . "</span>";
	}


	private function render_room_schedule_summary(?array $summary)
	{
		if (empty($summary)) {
			return '';
		}

		$rows = [];
		if ($summary['current']) {
			$rows[] = $this->render_room_schedule_summary_row(lang('booking.room.summary.current'), $summary['current'], 'active');
		}
		if ($summary['next']) {
			$rows[] = $this->render_room_schedule_summary_row(lang('booking.room.summary.next'), $summary['next'], 'upcoming');
		}

		if (!empty($rows)) {
			$classes = ['cps-room-schedule-summary'];
			if (count($rows) > 1) {
				$classes[] = 'cps-room-schedule-summary-inline';
			}
			if (!$summary['current'] && $summary['next']) {
				$classes[] = 'cps-room-schedule-summary-next-only';
			}

			return "<div class='" . implode(' ', $classes) . "'>" . implode('', $rows) . "</div>";
		}

		return "<div class='cps-room-schedule-summary cps-room-schedule-summary-free'>" . html_escape(lang('booking.room.summary.none')) . "</div>";
	}


	private function render_room_schedule_summary_row($label, array $item, $status)
	{
		$time = html_escape($item['time_label'] ?? '');
		$user = !empty($item['user_label'])
			? "<span class='cps-room-schedule-user'>" . html_escape($item['user_label']) . "</span>"
			: '';
		$subject = !empty($item['period_label'])
			? "<span class='cps-room-schedule-subject' title='" . html_escape($item['period_label']) . "'>" . html_escape($item['period_label']) . "</span>"
			: '';

		return "<div class='cps-room-schedule-row cps-room-schedule-row-{$status}'>"
			. "<span class='cps-room-schedule-label'>" . html_escape($label) . "</span>"
			. "<span class='cps-room-schedule-time'>{$time}</span>"
			. $user
			. $subject
			. "</div>";
	}


	private function current_datetime(): \DateTimeImmutable
	{
		$fake_now = getenv('APP_FAKE_NOW');
		if ($fake_now) {
			return new \DateTimeImmutable($fake_now);
		}

		return new \DateTimeImmutable();
	}


	private function get_room_group_name($room)
	{
		if (isset($room->group) && isset($room->group->name)) {
			return $room->group->name;
		}
		if (isset($room->group__name)) {
			return $room->group__name;
		}
		return null;
	}


	private function get_floor_accent_class($room)
	{
		$group_id = isset($room->room_group_id) ? (int) $room->room_group_id : 0;
		if ($group_id <= 0) {
			return 'cps-floor-accent-0';
		}

		$accent = (($group_id - 1) % 8) + 1;
		return "cps-floor-accent-{$accent}";
	}


	private function parse_slots($template)
	{
		$vars = [];

		$classes = [
			'bookings-grid-slot',
		];

		$highlight = null;
		$params = $this->context->get_query_params();
		if (isset($params['highlight'])) {
			$highlight = (int) $params['highlight'];
		}

		foreach ($this->context->slots as $slot) {

			$slot_classes = [
				sprintf('booking-status-%s', $slot->status),
				sprintf('booking-status-%s-%s', $slot->status, $slot->reason),
			];

			if ($slot->booking && $slot->booking->booking_id == $highlight) {
				$slot_classes[] = 'highlight';
			}

			$class_str = implode(' ', array_merge($classes, $slot_classes));

			$view_name = sprintf('bookings_grid/table/slot/%s_%s', $slot->status, $slot->reason);
			$view_name = rtrim($view_name, '_');

			$view_data = [
				'class' => $class_str,
				'slot' => $slot,
				'context' => $slot->context,
				'extended' => FALSE,
			];

			$view_data = array_merge($view_data, $slot->view_data);

			$view = $this->CI->load->view($view_name, $view_data, TRUE);

			$vars[$slot->key] = $view;
		}

		$this->CI->load->library('parser');
		$html = $this->CI->parser->parse_string($template, $vars, TRUE);

		return $html;
	}


}
