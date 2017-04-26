<?php
/**
 * Created by PhpStorm.
 * User: putra.liowono
 * Date: 4/25/2016
 * Time: 3:06 PM
 */
    class Pages extends CI_Controller
    {

        function __construct()
        {
            parent::__construct();
            $this->load->helper('url');
        }

        public function index()
        {
            $this->load->view('csv/'.'home'.'.html');
        }

        public function view()
        {
            $page = $this->uri->segment(1);
            if ( ! file_exists(APPPATH.'views/csv/'.$page.'.html'))
            {
                show_404();
            }
            $this->load->view('csv/'.$page.'.html');
        }
    }
?>