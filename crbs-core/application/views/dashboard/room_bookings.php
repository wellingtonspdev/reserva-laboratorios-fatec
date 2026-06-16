<?php if ($room_bookings): ?>

	<div class="w-full">

		<div class="bg-cps-white border border-cps-gray-border rounded-card p-6 shadow-sm">

			<h3 class="text-base font-bold text-cps-black m-0 mb-4"><?= lang('booking.in_my_rooms') ?></h3>
			<ul class="m-0 p-0 list-none">

				<?php
				foreach ($room_bookings as $booking) {

					$is_recurring = !empty($booking->repeat_id);
					$show_user = ($is_recurring)
						? has_permission(Permission::BK_RECUR_VIEW_OTHER_USERS, $booking->room->room_id)
						: has_permission(Permission::BK_SGL_VIEW_OTHER_USERS, $booking->room->room_id)
						;
					$show_notes = ($is_recurring)
						? has_permission(Permission::BK_RECUR_VIEW_OTHER_NOTES, $booking->room->room_id)
						: has_permission(Permission::BK_SGL_VIEW_OTHER_NOTES, $booking->room->room_id)
						;

					$date_str = date_output_long($booking->date);
					$time_str = date_output_time($booking->date);
					$period_name = html_escape($booking->period->name);
					$room_name = html_escape($booking->room->name);
					$user_name = !empty($booking->user->displayname)
						? html_escape($booking->user->displayname)
						: html_escape($booking->user->username);

					$time = "<span class='text-cps-gray-text'>({$time_str})</span>";

					$title_html = "<div class='font-bold text-cps-black text-sm'>{$date_str}</div>";

					if ($show_user) {
						$user_html = "<span class='text-cps-black'>{$user_name}</span>";
					} else {
						$user_html = "<em class='text-cps-gray-text'>User hidden</em>";
					}

					$room_url = "rooms/info/{$booking->room->room_id}";
					$room_link = anchor($room_url, $room_name, [
						'up-layer' => 'new drawer',
						'up-position' => 'left',
						'up-target' => '.room-info',
						'up-preload',
						'class' => 'text-cps-black hover:text-cps-red underline',
					]);
					$room_html = "<div class='text-sm text-cps-gray-text mt-1'>{$period_name} {$time} &middot; {$room_link} &middot; {$user_html}</div>";

					$notes_html = $show_notes && !empty($booking->notes)
						? '<div class="text-xs text-cps-gray-text mt-1 italic">' . html_escape($booking->notes) . '</div>'
						: '';

					$link = cps_icon('calendar', 'w-5 h-5 opacity-70 mt-1');
					$uri = 'bookings?date=%s&room=%d&room_group=%d&highlight=%d';
					$uri = sprintf($uri,
						$booking->date->format('Y-m-d'),
						$booking->room->room_id,
						$booking->room->room_group_id,
						$booking->booking_id
					);
					$anchor = anchor($uri, $link, ['class' => 'flex-shrink-0 mr-3']);

					echo "<li class='flex items-start py-3 border-b border-cps-gray-border last:border-0'>{$anchor}<div>{$title_html}{$room_html}{$notes_html}</div></li>";
				}
				?>
			</ul>

		</div>

	</div>

<?php endif; ?>
