<?php

defined('BASEPATH') || define('BASEPATH', __DIR__ . '/../crbs-core/system/');
require_once __DIR__ . '/../crbs-core/vendor/autoload.php';

use app\components\bookings\RoomOccupancySummary;

function assert_same($expected, $actual, $message)
{
	if ($expected !== $actual) {
		fwrite(STDERR, "FAIL: {$message}\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
		exit(1);
	}
}

function user_obj($displayname, $username = 'user')
{
	return (object) [
		'displayname' => $displayname,
		'username' => $username,
	];
}

function period_obj($name, $start, $end)
{
	return (object) [
		'name' => $name,
		'time_start' => $start,
		'time_end' => $end,
	];
}

function room_obj($room_id, $name = 'Sala 20')
{
	return (object) [
		'room_id' => $room_id,
		'name' => $name,
	];
}

function slot_obj($room_id, $date, $period, $status = 'available', $user = null, $show_user = true)
{
	$booking = null;
	if ($status === 'booked') {
		$booking = (object) [
			'user' => $user,
		];
	}

	return (object) [
		'status' => $status,
		'date' => (object) ['date' => $date],
		'period' => $period,
		'room' => room_obj($room_id),
		'booking' => $booking,
		'view_data' => [
			'show_user' => $show_user,
		],
	];
}

$room = room_obj(20);
$now = new DateTimeImmutable('2026-04-29 10:30:00');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('1/Manha', '07:40:00', '08:30:00')),
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('THIAGO AZEVEDO')),
	slot_obj(20, '2026-04-29', period_obj('5/Manha', '11:10:00', '12:10:00'), 'booked', user_obj('ALEX ARNOSO')),
], $room, $now);

assert_same('active', $summary['state'], 'current booked slot should set active state');
assert_same('THIAGO AZEVEDO', $summary['current']['user_label'], 'current slot should expose visible user');
assert_same('10:20 - 11:10', $summary['current']['time_label'], 'current slot should expose formatted time');
assert_same('ALEX ARNOSO', $summary['next']['user_label'], 'next slot should expose next visible user');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('HIDDEN USER'), false),
], $room, $now);

assert_same('Reservado', $summary['current']['user_label'], 'hidden users should be masked as reserved');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00')),
	slot_obj(20, '2026-04-29', period_obj('5/Manha', '11:10:00', '12:10:00'), 'booked', user_obj('ALEX ARNOSO')),
], $room, $now);

assert_same('upcoming', $summary['state'], 'available current period with later booking should set upcoming state');
assert_same(null, $summary['current'], 'available current period should not expose a current booking');
assert_same('ALEX ARNOSO', $summary['next']['user_label'], 'free state should still expose next booking');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(21, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('OTHER ROOM')),
], $room, $now);

assert_same('free', $summary['state'], 'slots from other rooms should be ignored');
assert_same(null, $summary['next'], 'slots from other rooms should not become next booking');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('THIAGO AZEVEDO')),
], $room, new DateTimeImmutable('2026-04-29 10:20:00'));

assert_same('active', $summary['state'], 'slot should be active exactly at period start');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('THIAGO AZEVEDO')),
	slot_obj(20, '2026-04-29', period_obj('5/Manha', '11:10:00', '12:10:00'), 'booked', user_obj('ALEX ARNOSO')),
], $room, new DateTimeImmutable('2026-04-29 11:10:00'));

assert_same('active', $summary['state'], 'next slot should be active exactly when previous period ends');
assert_same('ALEX ARNOSO', $summary['current']['user_label'], 'boundary at period end should move to next booking');

$summary = RoomOccupancySummary::forRoom([
	slot_obj(20, '2026-04-29', period_obj('4/Manha', '10:20:00', '11:10:00'), 'booked', user_obj('THIAGO AZEVEDO')),
], $room, new DateTimeImmutable('2026-04-29 12:10:00'));

assert_same('free', $summary['state'], 'room should be free after last booking ends');
assert_same(null, $summary['next'], 'room should not expose next booking after last booking ends');

fwrite(STDOUT, "RoomOccupancySummaryTest passed\n");
