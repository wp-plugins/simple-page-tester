<?php

/**
 *
 */
class SPT_Utils_Tour {

    private static $instance = null;

    const STATUS_OPEN = 'open';
    const STATUS_CLOSE = 'close';

    private $urls;
    private $screens;

    private function __construct() {
        $this->urls = array(
            'plugins' => admin_url('plugins.php'),
            'edit-spt' => admin_url('edit.php?post_type=spt'),
            'edit-page' => admin_url('edit.php?post_type=page'),
            'edit-post' => admin_url('edit.php?post_type=post'),
        );

        $this->screens = array(
            'plugins' => array(
                'elem' => '#menu-plugins',
                'html' => '<h3>Guided Tour</h3><p>Welcome to Simple Page Tester. Would you like to go on a guided tour of the plugin?</p>',
                'prev' => null,
                'next' => $this->urls['edit-spt'],
            ),
            'edit-spt' => array(
                'elem' => '#menu-posts-spt',
                'html' => '<h3>Viewing Your Split Tests</h3><p>The Split Tests menu shows you all the split tests you have in the system along with their status.</p><p>Click on an individual test to see more details about the test including controls for starting/stopping a test and view statistics.</p>',
                'prev' => $this->urls['plugins'],
                'next' => $this->urls['edit-page'],
            ),
            'edit-page' => array(
                'elem' => '#menu-pages',
                'html' => '<h3>Choosing A Page For Testing</h3><p>We have made it super simple to get started with testing a page.</p><p>Simply choose a page to edit, then when you get to the edit screen you will see a box on the bottom right saying "Setup New Split Test".</p><p>Click on that button and follow the instructions on screen.</p>',
                'prev' => $this->urls['edit-spt'],
                'next' => $this->urls['edit-post'],
            ),
            'edit-post' => array(
                'elem' => '#menu-posts',
                'html' => '<h3>You Can Also Split Test Posts</h3><p>You can also use Simple Page Tester to split test Posts on your site as well as Pages.</p><p>The Premium add-on also lets you split test other Custom Post Types such as Products, Portfolios, or whatever your site uses.</p><p><a href="https://simplepagetester.com/premium/">Click here to see Premium add-on features &rarr;</a></p><p><b>We hope you enjoy using Simple Page Tester!</b></p>',
                'prev' => $this->urls['edit-page'],
                'next' => null,
            ),
        );
    }

    /**
     * Get the only instance of the class.
     *
     * @return SPT_Utils_Tour
     */
    public static function instance() {
        if ( !self::$instance )
            self::$instance = new SPT_Utils_Tour();

        return self::$instance;
    }

    public function getCurrentScreen() {
        $screen = get_current_screen();

        if (!empty($this->screens[$screen->id]))
            return $this->screens[$screen->id];

        return false;
    }

    public function updateOptions() {
        if(get_option('sptTourStatus') === false)
            update_option('sptTourStatus', 'open');
    }

    public function deleteOptions() {
        delete_option('sptTourStatus');
    }
}
