<?php
class Csv extends CI_Controller
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
        
        $nameData = $this->input->post("title");
        $nameOnDb = $this->Csv_model->select_trans();

        if(empty($nameData)||$nameData == 'Eg: Kode TRX Validation Request 160314')
            echo "<script>alert('Please, fill the title'); window.location.href='/index.php/home'; </script>";

        if (!empty($nameOnDb)) {
            foreach ($nameOnDb as $value) {
                $name = $value['ListName'];
                if ($nameData == $name) {
                    echo "<script>alert('The title already added'); window.location.href='/index.php/home'; </script>";
                }
                else continue;
            }
        }
        
        $config['sess_expire_on_close'] = true;
        $config['upload_path'] = './temp_upload/';
        $config['allowed_types'] = 'xls|csv|xlsx';
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload())
        {
            $data['error'] = $this->upload->display_errors('','');
            $err = $data['error'];
            echo "<script>alert('$err'); window.location.href='/index.php/home'; </script>";
        }else {
            $file_data = $this->upload->data();
            $typeFile = $file_data['file_ext'];
            $file_path = './temp_upload/' . $file_data['file_name'];
            if($typeFile == ".csv" || $typeFile == ".xlsx") {
                if ($this->csvimport->get_array($file_path)) {
                    $csv_array = $this->csvimport->get_array($file_path);
                    $insert_data = array();
                    for($n=0; $n<count($csv_array); $n++){
                        $insert_data[]= array(
                            'KodeTrx' => str_pad($csv_array[$n]['KodeTrx'], 8, 0, STR_PAD_LEFT),
                            'ListName' => $nameData,
                            'Date_time' => date('Y-m-d H:i:s')
                        );
                    }
                    if(count($csv_array) > 1) {
                        $this->Csv_model->insert_trans_b('azswd01.BI_Data.dbo.tempTransGA', $insert_data);
                    }else{
                        $this->Csv_model->insert_trans('azswd01.BI_Data.dbo.tempTransGA', $insert_data);
                    }
                    echo "<script>alert('Your file uploaded'); window.location.href='/index.php/home'; </script>";
                }else {
                    $data['error'] = "Error occured";
                    echo "<script>alert('Data Error'); window.location.href='/index.php/home'; </script>";
                }
            }
            else{
                $this->excel_reader->setOutputEncoding('CP1251');
                $file_data = $this->upload->data();
                $file =  $file_data['full_path'];
                $this->excel_reader->read($file);
                error_reporting(E_ALL ^ E_NOTICE);
                $data = $this->excel_reader->sheets[0];
                $insert_data = array();
                for ($n = 2; $n <= $data['numRows']; $n++) {
                    $insert_data[]= array(
                        'KodeTrx' => str_pad($data['cells'][$n][1], 8, 0, STR_PAD_LEFT),
                        'ListName' => $nameData,
                        'Date_time' => date('Y-m-d H:i:s')
                    );
                }
                if($data['numRows'] > 2) {
                    $this->Csv_model->insert_trans_b('azswd01.BI_Data.dbo.tempTransGA', $insert_data);
                }else{
                    $this->Csv_model->insert_trans('azswd01.BI_Data.dbo.tempTransGA', $insert_data);
                }
                echo "<script>alert('Your file uploaded'); window.location.href='/index.php/home'; </script>";
            }
        }
    }
}
?>