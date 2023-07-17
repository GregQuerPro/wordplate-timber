<?php

// Register scripts and styles.
add_action('wp_enqueue_scripts', function () {
	$manifestPath = get_theme_file_path('assets/manifest.json');

	if (
		wp_get_environment_type() === 'local' &&
		is_array(wp_remote_get('http://localhost:5173/')) // is Vite.js running
	) {
		wp_enqueue_script('vite', 'http://localhost:5173/@vite/client', [], null);
		wp_enqueue_script('wordplate', 'http://localhost:5173/resources/scripts/index.js', [], null);
	} elseif (file_exists($manifestPath)) {
		$manifest = json_decode(file_get_contents($manifestPath), true);
		wp_enqueue_script('wordplate', get_theme_file_uri('assets/' . $manifest['resources/scripts/index.js']['file']), [], null);
		wp_enqueue_style('wordplate', get_theme_file_uri('assets/' . $manifest['resources/scripts/index.css']['file']), [], null);
	}
});

// Load scripts as modules.
add_filter('script_loader_tag', function (string $tag, string $handle, string $src) {
	if (in_array($handle, ['vite', 'wordplate'])) {
		return '<script type="module" src="' . esc_url($src) . '" defer></script>';
	}

	return $tag;
}, 10, 3);

/**
 * This ensures that Timber is loaded and available as a PHP class.
 * If not, it gives an error message to help direct developers on where to activate
 */

if (!class_exists('Timber')) {

	add_action(
		'admin_notices',
		function () {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url(admin_url('plugins.php#timber')) . '">' . esc_url(admin_url('plugins.php')) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function ($template) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);
	return;
}

/**
 * Sets the directories (inside your theme) to find .twig files
 */
Timber\Timber::$dirname = array('templates', 'views');

/**
 * By default, Timber does NOT autoescape values. Want to enable Twig's autoescape?
 * No prob! Just set this value to true
 */
Timber\Timber::$autoescape = true;

/**
 * We're going to configure our theme inside of a subclass of Timber\Site
 * You can move this to its own file and include here via php's include("MySite.php")
 */
class StarterSite extends Timber\Site
{
	/** Add timber support. */
	public function __construct()
	{
		add_action('after_setup_theme', array($this, 'theme_supports'));
		add_filter('timber/context', array($this, 'add_to_context'));
		add_filter('timber/twig', array($this, 'add_to_twig'));
		add_action('init', array($this, 'register_post_types'));
		add_action('init', array($this, 'register_taxonomies'));
		parent::__construct();
	}
	/** This is where you can register custom post types. */
	public function register_post_types()
	{
	}
	/** This is where you can register custom taxonomies. */
	public function register_taxonomies()
	{
	}

	/** This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context($context)
	{
		// Si on a besoin d'avoir des variables globales
		$context['menu']  = new Timber\Menu();
		$context['site']  = $this;
		return $context;
	}

	public function theme_supports()
	{
		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support('menus');
	}

	/*
	 * This is where you can add your own functions to twig.
	 */
	public function add_to_twig($twig)
	{
		$twig->addExtension(new Twig\Extension\StringLoaderExtension());
		$twig->addFilter(new Twig\TwigFilter('myfoo', array($this, 'myfoo')));
		return $twig;
	}
}

new StarterSite();
