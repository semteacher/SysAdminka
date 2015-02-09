<?php
namespace App\Controller;
use App\Controller\AppController;
include_once('Component/CsvComponent.php');
use CsvComponent;
class ViewsController extends AppController
{
    private $schools;
    private $specials;
    private $status;
    private $options;

    public function beforeFilter(){
        $this->loadModel('Students');

        $this->schools = $this->Students->Schools->find('list');
        $this->specials = $this->Students->Specials->find('list');
        $this->status = $this->Students->Status->find('list');
        $this->set('schools',$this->schools);
        $this->set('specials',$this->specials);
        $this->set('status',$this->status);
        $this->options = [
            'length' => 0,
            'delimiter' => ';',
            'enclosure' => '"',
            'escape' => '\\',
            'headers' => true,
            'text' => false,
        ];


    }

    public function moodle(){
        if ($this->request->is('post')) {
            $Csv = new CsvComponent($this->options);
            $data = $this->Students->find()->where([
                        ' school_id = '.$this->request->data['school_id'].
                        ' AND special_id = '.$this->request->data['special_id'].
                        ' AND grade_level = '.$this->request->data['grade_level'].
                        ' AND status_id = '.$this->request->data['status_id']]);
            $data =json_decode(json_encode($data), true);
            if (count($data)>0){
                $Csv->exportCsv(ROOT.DS."webroot".DS."files/".$_SESSION['Auth']['User']['id'].".csv", array($data), $this->options);
                return $this->redirect($_SERVER['domain']."/files/".$_SESSION['Auth']['User']['id'].".csv");
            }else{
                $this->Flash->error(__('No users'));
            }
        }
    }

    public function deanery(){

        if ($this->request->is('post')) {
            $data = $this->Students->find()->where([
                ' school_id = '.$this->request->data['school_id'].
                ' AND special_id = '.$this->request->data['special_id'].
                ' AND grade_level = '.$this->request->data['grade_level'].
                ' AND status_id = '.$this->request->data['status_id']])->order('groupnum ASC');
            $data =json_decode(json_encode($data), true);
            if (count($data)>0){
                $this->set('students',$data);
                $this->render('deanery');
            }else{
                $this->Flash->error(__('No users'));
            }
        }
        $this->render('moodle');
    }
}