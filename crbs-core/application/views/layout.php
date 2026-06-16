<?php

//

$global_menu = $this->menu_model->global();
$footer_menu = $global_menu;
if (has_setup_permission()) {
	$footer_menu[] = [
		'label' => lang('app.whats_new') . ' ' . $this->changelog->get_indicator_markup(),
		'url' => site_url('changelog'),
		'icon' => 'cake.png',
		'ext' => true,
	];
}

?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Craig A Rodway">
<meta name="robots" content="noindex,nofollow">
<title><?= html_escape($title) ?> | classroombookings</title>
<?php
if (CRBS_MANAGED && setting('logo')) {
	echo "<link rel='preconnect' href='https://crbsimg.b-cdn.net'>\n";
}
$conf_suffix = '';
if (CRBS_MANAGED && config_item('asset_cdn_host')) {
	echo "<link rel='preconnect' href='https://cdn.classroombookings.net'>\n";
	$conf_suffix = '-cloud';
}
foreach ($css as $css_conf) {
	$url = asset_url($css_conf['path'], true);
	$fmt = "<link rel='stylesheet' type='text/css' media='%s' href='%s'>\n";
	echo sprintf($fmt, $css_conf['media'], $url);
}
foreach ($hs as $hs_config) {
	$src = asset_url($hs_config['path'], $hs_config['version']);
	$attrs = [];
	$attrs['type'] = 'text/hyperscript';
	if (isset($hs_config['defer']) && $hs_config['defer'] === true) {
		$attrs['defer'] = '';
	}
	echo script_src($src, $attrs);
}
foreach ($js as $js_conf) {
	$src = asset_url($js_conf['path'], $js_conf['version']);
	$attrs = [];
	if (isset($js_conf['defer']) && $js_conf['defer'] === true) {
		$attrs['defer'] = '';
	}
	echo script_src($src, $attrs);
}
?>
<script src="https://unpkg.com/htmx.org@1.9.10" defer></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?= asset_url('assets/css/dist/style.css', true) ?>?v=<?= @filemtime(FCPATH . 'assets/css/dist/style.css') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= asset_url('assets/brand/apple-touch-icon.png', true) ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset_url('assets/brand/favicon-32x32.png', true) ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= asset_url('assets/brand/favicon-16x16.png', true) ?>">
<link rel="manifest" href="<?= asset_url(sprintf('assets/brand/site%s.webmanifest', $conf_suffix), true) ?>">
<link rel="mask-icon" href="<?= asset_url('assets/brand/safari-pinned-tab.svg', true) ?>" color="#ff6400">
<link rel="shortcut icon" href="<?= asset_url('assets/brand/favicon.ico', true) ?>">
<meta name="msapplication-TileColor" content="#ff6400">
<meta name="msapplication-config" content="<?= asset_url(sprintf('assets/brand/browserconfig%s.xml', $conf_suffix), true) ?>">
<meta name="theme-color" content="#ff6400">
<script type="text/javascript">
function ready(fn) {
	if (document.readyState !== "loading") {
		setTimeout(fn);
	} else {
		document.addEventListener("DOMContentLoaded", fn);
	}
}

var h = document.getElementsByTagName("html")[0];
(h ? h.classList.add('js') : h.className += ' ' + 'js');
var BASE_URL = "<?= base_url() ?>";
</script>

</head>
<body class="font-sans bg-cps-gray-light text-cps-black pb-20 md:pb-0 flex flex-col min-h-screen">

	<?php
	if (setting('maintenance_mode') == 1) {
		$message = setting('maintenance_mode_message');
		if (empty($message)) {
			$message = lang('app.maintenance_message');
		}
		echo "<div class='maintenance-wrapper'>";
		echo "<div class='outer'>";
		echo html_escape($message);
		echo "</div>";
		echo "</div>";
	}
	?>
	<?php $hide_global_menu = !empty($hide_global_menu); ?>
	<?php $hide_footer_menu = !empty($hide_footer_menu); ?>
	<?php $hide_mobile_bottom_nav = !empty($hide_mobile_bottom_nav); ?>
	<header class="bg-cps-red text-cps-white" x-data="{ menuOpen: false }">
		<div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">

			<div class="min-w-0 flex items-center gap-2">
				<?php
				$logo = img([
					'src' => asset_url('assets/images/logos/fatec-itaquera-white.svg'),
					'alt' => 'Logo Fatec',
					'class' => 'cps-header-logo',
				]);
				$name = '';
				$attrs = 'class="cps-header-brand"';
				if (config_item('is_installed')) {
					$name = setting('name');
				}
				if (!empty($name) && $name !== 'classroombookings') {
					$name = '<span class="cps-header-title">' . html_escape($name) . '</span>';
				} else {
					$name = '<span class="cps-header-title">' . html_escape(lang('app.site_name')) . '</span>';
				}
				echo anchor('/', $logo . $name, $attrs);
				?>
			</div>

			<?php if (!$hide_global_menu): ?>
				<button @click="menuOpen = !menuOpen" class="md:hidden flex h-11 w-11 shrink-0 items-center justify-center text-cps-white text-2xl bg-transparent border-none cursor-pointer p-1 hover:text-gray-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="<?= html_escape(lang('app.nav.open_menu')) ?>">
					<svg x-show="!menuOpen" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
					<svg x-show="menuOpen" class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
				</button>
			<?php endif; ?>

			<?php if (!$hide_global_menu): ?>
				<nav class="hidden md:flex items-center gap-4" :class="{ '!flex flex-col absolute top-14 left-0 w-full bg-cps-red border-b border-red-800 px-4 py-3 shadow-lg z-40': menuOpen }">
					<?php
					if ( ! empty($global_menu)) {
						foreach ($global_menu as $idx => $item) {
							$icon = cps_icon($item['icon'], 'w-4 h-4 inline flex-shrink-0', strip_tags((string) $item['label']));
							$label = $icon . ' <span class="text-sm font-medium">' . html_escape($item['label']) . '</span>';
							echo anchor($item['url'], $label, 'class="flex items-center gap-1 text-cps-white hover:text-gray-200 hover:bg-red-800 px-2 py-1 rounded no-underline"');
						}
					}
					?>
				</nav>
			<?php endif; ?>

		</div>
	</header>

	<main class="max-w-7xl mx-auto px-4 py-6 w-full flex-grow flex flex-col" up-main>

		<?php if (isset($midsection) || (isset($showtitle) && !empty($showtitle))): ?>
			<div class="mb-6">
				<h1 class="text-2xl font-bold text-cps-black">
					<?php 
					if (isset($midsection)) {
						echo $midsection;
					} elseif (isset($showtitle) && !empty($showtitle)) {
						echo html_escape($showtitle);
					}
					?>
				</h1>
			</div>
		<?php endif; ?>

		<div class="flex-grow">
			<?php echo $body; ?>
		</div>

		<?php if (!$hide_footer_menu): ?>
			<footer class="mt-8 border-t border-cps-gray-border">
				<div class="py-4 flex flex-col md:flex-row justify-between items-center gap-4">
					<div class="flex flex-wrap gap-3 justify-center md:justify-start">
						<?php
						if ( ! empty($footer_menu)) {
							foreach ($footer_menu as $idx => $item) {
								$icon = cps_icon($item['icon'], 'w-4 h-4 inline flex-shrink-0', strip_tags((string) $item['label']));
								$attrs = 'class="flex items-center gap-1 text-sm text-cps-gray-text hover:text-cps-red no-underline"';
								if (isset($item['ext']) && $item['ext']) {
									$attrs .= " target='_blank' rel='noopener' ";
								}
								if (isset($item['id'])) {
									$attrs .= " id='{$item['id']}' ";
								}
								if (isset($item['attrs'])) {
									$attrs .= ' ' . $item['attrs'] . ' ';
								}
								$label = $icon . $item['label'];
								$link = anchor($item['url'], $label, $attrs);
								if ($item['url'] === '#') {
									$link = str_replace(site_url('#'), '#', $link);
								}
								echo "{$link}\n";
							}
						}
						?>
					</div>
					<div class="text-xs text-cps-gray-text text-center md:text-right">
						<a href="https://www.classroombookings.com/" target="_blank" class="text-cps-gray-text hover:text-cps-red no-underline">classroombookings</a>
						<?= strtolower(lang('app.version')) ?>
						<?= VERSION ?>.
						&copy; <?= date('Y') ?> Craig A Rodway.
						<br>
						<?php
						$fmt = "%s: %s %s";
						echo sprintf($fmt, lang('app.load_time'), $this->benchmark->elapsed_time(), lang('app.seconds'));
						?>
					</div>
				</div>
			</footer>
		<?php endif; ?>
	</main>

	<?php if (!$hide_mobile_bottom_nav): ?>
		<nav class="cps-mobile-bottom-nav">
			<?php
			if ( ! empty($global_menu)) {
				$bottom_menu = array_filter($global_menu, static function ($item) {
					return isset($item['url']) && $item['url'] !== 'logout';
				});
				$bottom_menu = array_slice($bottom_menu, 0, 5);
				foreach ($bottom_menu as $idx => $item) {
					$icon = cps_icon($item['icon'], 'w-5 h-5 mb-1', strip_tags((string) $item['label']));
					$label = $icon . '<span class="text-xs">' . html_escape($item['label']) . '</span>';
					echo anchor($item['url'], $label, 'class="nav-item"');
				}
			}
			?>
		</nav>
	<?php endif; ?>

</body>
</html>
