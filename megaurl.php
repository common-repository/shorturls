<?php
/**
Plugin name: Shorturls
Plugin URI: https://shorturlsez.com/
Description: shorturlsez is a free URL shortener which allows you to earn money for each visitor you bring your Shorted links.
Author: URL Shortener
Author URI: https://shorturlsez.com
License: GPL
Version: 1.0.0
 */

/**
 * Class shorturlsez
 * Singleton
 */
final class shorturlsez {
	private static $instance = null;

	public static function instance() {
		if ( static::$instance === null ) {
			static::$instance = new shorturlsez();
		}

		return static::$instance;
	}

	private function __construct() {
		// Add Menu
		if(isset($_REQUEST['action']) && is_callable($this->{$_REQUEST['action']})) {
			$this->{$_REQUEST['action']}();
		} else {
			$this->addMenu();
			$this->header();
		}
	}

	private function __clone() {
	}

	/**
	 * Alias of instance method
	 */
	public static function bootstrap() {
		static::instance();
	}

	protected function addMenu() {
		add_action( 'admin_menu', function () {
			add_submenu_page( 'options-general.php',
				'shorturlsez',
				'shorturlsez Settings',
				'administrator',
				'shorturlsez',
				[$this, 'adminTemplate']
			);
		} );
	}

	public function adminTemplate() {
		require __DIR__ . '/template.php';
	}

	public function saveData() {
		$data = $_REQUEST;
		unset($data['action'], $data['page']);
		update_option('shorturlsez', json_encode($data), true);
	}

	public function header() {
		add_action('wp_head', function() {
			?>
			<script type="text/javascript" id="shorturlsez-script">
(function(){
    function hashLink(link) {
        return 'https://shorturlsez.com/full/?api=' + token + '&url=' + btoa(link) + '&type=1';
    }
    var data = <?= get_option('shorturlsez'); ?>,
        token = data.token || '';
    if (!token) return null;
    var domains = (data.domains || '').split(/\r?\n/).map(function(domain) {return domain.trim()}),
        patterns = (data.patterns || '').split(/\r?\n/).map(function(pattern) {
            pattern = pattern.trim().split(' ');
            if (!pattern[0]) return null;
            return new RegExp(pattern[0], (pattern[1] || ''));
        }).filter(function(pattern) {return pattern});

    domains.push('shorturlsez.com');
    domains.push('www.shorturlsez.com');

    setInterval(function() {
        var aTags = document.querySelectorAll('a:not(.hashed)');
        aTags.forEach(function(el) {
            var href = el.getAttribute('href') || '';
            if (!href || href[0] === '#' || href[0] === '!' || href.substr(0,11) === 'javascript:') return false;
            var hashed = false;
            if (domains.indexOf(el.hostname) === -1) {
                el.href = hashLink(el.href);
                hashed = true;
                return null;
            }
            
            patterns.forEach(function(pattern) {
                if(pattern.test(el.href)) {
                    el.href = hashLink(el.href);
                    hashed = true;
                }
            });

            if (hashed) el.classList.add('hashed');
        });
    }, 200);
})();
			</script>
			<?php
		});
	}
}

shorturlsez::bootstrap();
