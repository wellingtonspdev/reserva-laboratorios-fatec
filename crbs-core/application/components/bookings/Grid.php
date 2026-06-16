<?php

namespace app\components\bookings;

defined('BASEPATH') OR exit('No direct script access allowed');

use app\components\bookings\grid\Controls;
use app\components\bookings\grid\Header;
use app\components\bookings\grid\Table;


class Grid
{

	private \MY_Controller $CI;

	private Context $context;
	private Controls $controls;
	private Header $header;
	private Table $table;

	public function __construct(Context $context)
	{
		$this->CI =& get_instance();

		$this->CI->load->model([
			'sessions_model',
			'dates_model',
			'users_model',
			'rooms_model',
			'periods_model',
			'weeks_model',
		]);

		$this->context = $context;

		$this->controls = new Controls($context);
		$this->header = new Header($context);
		$this->table = new Table($context);
	}


	public function render()
	{
		$controls = $this->controls->render();
		$header = ($this->context->display_type === 'day')
			? ''
			: $this->header->render();

		$group_buttons = $this->render_group_buttons();
		$body = $this->render_body();
		$footer = $this->render_footer();
		$legend = $this->render_legend();

		$params = $this->context->get_query_params();
		$query_str = http_build_query($params);

		// Multi-select form: create bookings
		//
		$form_attrs = [
			'up-layer' => 'new modal',
			'up-size' => 'large',
			'up-target' => '.bookings-create',
			'up-dismissable' => 'button key',
			'id' => 'form_create_multi',
		];
		$form_hidden = ['step' => 'selection', 'params' => $query_str];
		$form_open = form_open($this->context->base_uri . '/create/multi', $form_attrs, $form_hidden);
		$form_close = form_close();
		$create_form = $form_open . $form_close;

		// Multi-select form: cancel existing bookings
		//
		$form_attrs = [
			'up-layer' => 'new modal',
			'up-size' => 'large',
			'up-target' => '.bookings-cancel-multi',
			'up-dismissable' => 'button key',
			'id' => 'form_cancel_multi',
		];
		$form_hidden = ['step' => 'selection', 'params' => $query_str];
		$form_open = form_open($this->context->base_uri . '/cancel_multi', $form_attrs, $form_hidden);
		$form_close = form_close();
		$cancel_form = $form_open . $form_close;

		$style = $this->render_style();

		$out = "{$controls}\n{$header}\n{$group_buttons}\n{$body}\n{$footer}\n{$legend}\n{$create_form}\n{$cancel_form}\n";

		return "{$style}<div id='bookings_grid' up-hungry>{$out}</div>";
	}


	private function render_group_buttons()
	{
		if ($this->context->display_type == 'day' || $this->context->display_type == 'room') return '';

		$items = [];
		$has_group = $this->context->room_group !== FALSE;
		$params = $this->context->get_query_params();

		foreach ($this->context->room_groups as $group) {

			$is_open = $has_group && $this->context->room_group->room_group_id == $group->room_group_id;

			$query = $params;
			$query['room_group'] = $group->room_group_id;
			$query_str = http_build_query($query);

			$url = site_url($this->context->base_uri) . '?' . $query_str;

			$items[] = [
				'url' => $url,
				'title' => sprintf('%s <span>(%d)</span>', html_escape($group->name), $group->room_count),
				'active' => $is_open,
				'attrs' => 'up-follow up-preload'
			];
		}
		return buttonlist($items);
	}


	/**
	 * Render the main content area.
	 *
	 * If an exception is present in the context, the error is displayed instead of the table.
	 *
	 */
	public function render_body()
	{
		// Check for any errors and render it instead of the table.
		if ($this->context->exception) {
			return msgbox('error', $this->context->exception->getMessage());
		}

		$ms = $this->render_multiselect_controls('header');

		$table = $this->table->render();

		return $ms . $table;
	}


	/**
	 * Render the footer. This includes the legend/key, and recurring bookings controls.
	 *
	 */
	public function render_footer()
	{
		if ($this->context->exception) {
			return '';
		}

		return $this->render_multiselect_controls('footer');
	}


	private function render_multiselect_controls($position = '')
	{
		$mb = ($position === 'header') ? 'mb-2' : 'mt-2 mb-8';

		$toggle_true = '<span class="multi-select-content" style="display:none" data-multi="true">&#9745;</span>';
		$toggle_false = '<span class="multi-select-content" data-multi="false">&#9745;</span>';

		$toggle_btn = form_button([
			'type' => 'button',
			'data-script' => 'on click trigger toggle_ms on .bookings-grid-cards',
			'content' => $toggle_false . $toggle_true . ' ' . lang('booking.toggle_multi_select'),
			'class' => 'cps-btn-secondary text-xs px-2 py-1',
		]);

		$create_btn = form_button([
			'type' => 'submit',
			'class' => 'multi-select-content cps-btn text-xs px-2 py-1 bg-green-50 border border-green-500 text-green-700',
			'style' => 'display:none',
			'data-multi' => 'true',
			'form' => 'form_create_multi',
			'content' => '&#10004; ' . lang('booking.create_bookings') . '...',
		]);

		$cancel_btn = form_button([
			'type' => 'submit',
			'class' => 'multi-select-content cps-btn text-xs px-2 py-1 bg-red-50 border border-red-500 text-red-700',
			'style' => 'display:none',
			'data-multi' => 'true',
			'form' => 'form_cancel_multi',
			'content' => '&#10008; ' . lang('booking.action.cancel_bookings') . '...',
		]);

		return "<div class='flex justify-end items-center gap-2 p-2 {$mb} multi-select-controller'>{$create_btn}{$cancel_btn}{$toggle_btn}</div>";
	}


	/**
	 * Render table legend.
	 *
	 */
	public function render_legend()
	{
		$legend = html_escape(lang('booking.legend.legend'));
		$free = html_escape(lang('booking.legend.free'));
		$staff = html_escape(lang('booking.legend.staff'));
		$active = html_escape(lang('booking.room.status.active'));
		$upcoming = html_escape(lang('booking.legend.upcoming'));

		return "<div class='flex flex-wrap items-center justify-center gap-4 my-4 text-sm'>
			<strong>{$legend}:</strong>
			<span class='cps-legend-badge cps-legend-available'>{$free}</span>
			<span class='cps-legend-badge cps-legend-active'>{$active}</span>
			<span class='cps-legend-badge cps-legend-upcoming'>{$upcoming}</span>
			<span class='cps-legend-badge cps-legend-single'>{$staff}</span>
		</div>";
	}


	private function render_style()
	{
		if ( ! is_array($this->context->weeks)) return '';

		$css = '';
		foreach ($this->context->weeks as $week) {
			$css .= week_calendar_css($week);
		}

		return "<style type='text/css'>{$css}</style>";
	}



}
