<?php

namespace app\components\bookings\grid;

defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\bookings\Context;
use Permission;


class Controls
{


	// CI instance
	private $CI;


	// Context instance
	private $context;


	public function __construct(Context $context)
	{
		$this->CI =& get_instance();
		$this->context = $context;
	}


	/**
	 * Render the Date or Room selectors.
	 *
	 */
	public function render()
	{
		$display_view = $this->render_display_view();
		$floor_view = $this->render_floor_view();
		$session_view = $this->render_session_view();

		$row = "<div class='cps-controls-left'>{$display_view}</div>{$floor_view}<div class='cps-controls-right'>{$session_view}</div>";
		$group = "<div class='cps-controls-bar'>{$row}</div>";
		return $group;
	}


	private function render_display_view()
	{
		if ( ! $this->context->session) return '&nbsp;';

		$view = FALSE;

		switch ($this->context->display_type) {

			case 'day':

				$query_params = $this->context->get_query_params();
				$query_params['selected_date'] = $query_params['date'];
				unset($query_params['date']);

				$data = [
					'query_params' => $query_params,
					'form_action' => $this->context->base_uri,
					'datetime' => $this->context->datetime,
					'prev_url' => $this->get_navigation_url('prev'),
					'next_url' => $this->get_navigation_url('next'),
					'week_name' => $this->context->timetable_week ? $this->context->timetable_week->name : '',
				];

				$view = 'bookings_grid/controls/day';

				break;

			case 'room':

				$rooms = [];
				foreach ($this->context->rooms as $room) {
					$rooms[ $room->room_id ] = html_escape($room->name);
				}

				$query_params = $this->context->get_query_params();
				unset($query_params['room']);

				$data = [
					'room' => $this->context->room,
					'rooms' => $rooms,
					'query_params' => $query_params,
					'form_action' => $this->context->base_uri,
					'datetime' => $this->context->datetime,
					'week_start' => $this->context->week_start,
				];

				$view = 'bookings_grid/controls/room';

				break;
		}

		if ($view) {
			return $this->CI->load->view($view, $data, TRUE);
		}

		return '';
	}


	private function get_navigation_url($direction)
	{
		$date = match ($direction) {
			'prev' => $this->context->prev_date,
			'next' => $this->context->next_date,
			default => FALSE,
		};

		if ( ! $date) {
			return '';
		}

		$params = $this->context->get_query_params();
		$params['date'] = $date->format('Y-m-d');
		$params['dir'] = $direction;

		return site_url($this->context->base_uri) . '?' . http_build_query($params);
	}


	private function render_floor_view()
	{
		$room_groups = $this->context->room_groups;
		if ($this->context->display_type !== 'day' || ! $room_groups) {
			return '';
		}

		$params = $this->context->get_query_params();
		$has_group = $this->context->room_group !== FALSE;
		$active_label = lang('booking.filter.floors');
		$items = [];

		foreach ($room_groups as $group) {
			$is_active = $has_group && $this->context->room_group->room_group_id == $group->room_group_id;
			if ($is_active) {
				$active_label = $group->name;
			}

			$query = $params;
			$query['room_group'] = $group->room_group_id;
			$url = site_url($this->context->base_uri) . '?' . http_build_query($query);
			$item_class = $is_active ? 'cps-floor-dropdown-item is-active' : 'cps-floor-dropdown-item';

			$attrs = [
				'class' => $item_class,
				'up-follow' => '',
				'up-preload' => '',
			];
			if ($is_active) {
				$attrs['aria-current'] = 'page';
			}

			$items[] = anchor($url, sprintf(
				'<span>%s</span><span class="cps-floor-dropdown-count">%d</span>',
				html_escape($group->name),
				(int) $group->room_count
			), $attrs);
		}

		$chevron = '<svg class="cps-icon-sm" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>';
		$floor_icon = '<svg class="w-4 h-4 text-cps-red flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M10 18h4"/></svg>';

		return "<div class='cps-controls-floor'>"
			. "<label class='cps-floor-label'>{$floor_icon} " . html_escape(lang('booking.filter.floors')) . ":</label>"
			. "<details class='cps-floor-dropdown'>"
			. "<summary class='cps-floor-dropdown-button'><span>" . html_escape($active_label) . "</span>{$chevron}</summary>"
			. "<div class='cps-floor-dropdown-menu'>" . implode('', $items) . "</div>"
			. "</details>"
			. "</div>";
	}


	private function render_session_view()
	{
		$show_all = ($this->context->user && has_permission(Permission::SYS_VIEW_ALL_SESSIONS))
			? TRUE
			: FALSE;


		if ($show_all) {

			$session_options = [];

			if ($this->context->active_sessions) {
				foreach ($this->context->active_sessions as $session) {
					$session_options[lang('booking.session.current')][$session->session_id] = $session->name;
				}
			}

			if ($this->context->past_sessions) {
				foreach ($this->context->past_sessions as $session) {
					$session_options[lang('booking.session.past')][$session->session_id] = $session->name;
				}
			}

		} else {

			// No available sessions: skip.
			if ( ! is_array($this->context->available_sessions)) {
				return '';
			}

			// Only 1 session *and* is current: skip.
			$num_sessions = count($this->context->available_sessions);
			$selected_is_current = ($this->context->session && $this->context->session->is_current == '1');

			if ($selected_is_current && $num_sessions == 1) {
				return '';
			}

			$session_options = ['' => ''];
			foreach ($this->context->available_sessions as $session) {
				$session_options[$session->session_id] = $session->name;
			}

		}

		$query_params = $this->context->get_query_params();

		$data = [
			'available_sessions' => $this->context->available_sessions,
			'selected_session_id' => $this->context->session_id,
			'form_action' => site_url('bookings/change_session'),
			'query_params' => $query_params,
			'session_options' => $session_options,
		];

		return $this->CI->load->view('bookings_grid/controls/session', $data, TRUE);
	}


}
