<?php
class Marcom extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Csv_model');
        $this->load->library('csvimport');
        $this->load->library('excel_reader');
        $this->load->helper(array('form', 'url', 'file'));
        $this->load->library('form_validation');
    }

    function do_upload()
    {
        //ini_set('upload_max_filesize','80M'); --> langsung di php.ini
        //ini_set('post_max_size','81M'); --> langsung di php.ini
        //ini_set('max_input_time','3600'); --> langsung di php.ini
        ini_set('max_execution_time','3600');
        ini_set('memory_limit','1024M');

        $config['upload_path'] = './temp_upload/';
        $config['allowed_types'] = 'xls';
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload())
        {
            $data['error'] = $this->upload->display_errors('','');
            $err = $data['error'];
            echo "<script>alert('$err'); window.location.href='/index.php/marcom'; </script>";
        }else {
            //import sheet 1
            $this->excel_reader->setOutputEncoding('CP1251');
            $file_data = $this->upload->data();
            $file =  $file_data['full_path'];
            $this->excel_reader->read($file);
            error_reporting(E_ALL ^ E_NOTICE);
            $insert_data = array();

            $data = $this->excel_reader->sheets[0];
            for ($n = 2; $n <= $data['numRows']; $n++) {
                $dateExc = str_replace('/', '-',$data['cells'][$n][4]);
                $dateDb = date('Y-m-d',strtotime($dateExc)-1);
                $insert_data[] = array(
                    'SourceMediumModif' => $data['cells'][$n][1],
                    'Source' => $data['cells'][$n][2],
                    'Medium' => $data['cells'][$n][3],
                    'DateTime' => $dateDb,
                    'Campaign' => $data['cells'][$n][5],
                    'Device' => $data['cells'][$n][6],
                    'UserType' => $data['cells'][$n][7],
                    'Session' => $data['cells'][$n][8],
                    'Bounces' => $data['cells'][$n][9],
                    'Users' => $data['cells'][$n][10],
                    'Pageviews' => $data['cells'][$n][11],
                    'UniquePageview' => $data['cells'][$n][12],
                    'AvgSessionDuration' => $data['cells'][$n][13],
                    'AvgTimeOnPage' => $data['cells'][$n][14],
                    'Clicks' => $data['cells'][$n][15],
                    'Impressions' => $data['cells'][$n][16],
                    'Cost' => $data['cells'][$n][17]
                );
            }
            if($data['numRows'] > 2) {
                $this->Csv_model->insert_trans_b('azswd01.BI_Data.dbo.tempMarcom', $insert_data);
            }else{
                $this->Csv_model->insert_trans('azswd01.BI_Data.dbo.tempMarcom', $insert_data);
            }
            //import sheet 2
            $insert_data2 = array();
            $dataSheet2 = $this->excel_reader->sheets[1];
            for ($n = 2; $n <= $dataSheet2['numRows']; $n++) {
                $dateExc = str_replace('/', '-',$dataSheet2['cells'][$n][4]);
                $dateDb = date('Y-m-d',strtotime($dateExc)-1);
                $insert_data2[] = array(
                    'SourceMediumModif' => $dataSheet2['cells'][$n][1],
                    'Source' => $dataSheet2['cells'][$n][2],
                    'Medium' => $dataSheet2['cells'][$n][3],
                    'DateTime' => $dateDb,
                    'Campaign' => $dataSheet2['cells'][$n][5],
                    'Trx' => str_pad($dataSheet2['cells'][$n][6], 8, 0, STR_PAD_LEFT)
                );
            }
            if($data['numRows'] > 2) {
                $this->Csv_model->insert_trans_b('azswd01.BI_Data.dbo.tempMarcomTrx', $insert_data2);
            }else{
                $this->Csv_model->insert_trans('azswd01.BI_Data.dbo.tempMarcomTrx', $insert_data2);
            }
            echo "<script>alert('Your file uploaded'); window.location.href='/index.php/marcom'; </script>";
        }
    }
}
?>