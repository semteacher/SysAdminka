<?php
namespace App\Controller;
use App\Controller\AppController;
include_once('Component/CsvComponent.php');
use CsvComponent;
use ZipArchive;
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
        $this->set('title','Export for Moodle');
        if ($this->request->is('post')) {
            $Csv = new CsvComponent($this->options);
            $data = $this->Students->find()->contain(['Schools', 'Specials'])->where([ //! For loading associations!
                        ' Students.school_id = '.$this->request->data['school_id'].
                        ' AND Students.special_id = '.$this->request->data['special_id'].
                        ' AND grade_level = '.$this->request->data['grade_level'].
                        ' AND status_id = '.$this->request->data['status_id']]);
            //$data= $data->contain(['Schools', 'Specials']); //! For loading associations!
            $data= $data->select([
                'username'=>'user_name',
                'password'=>'password',
                'firstname'=>'first_name',
                'lastname'=>'last_name',
                'email' => $data->func()->concat([
                    'user_name' => 'literal',
                    '@',
                    'tdmu.edu.ua'
                ]),
                'course1' => $data->func()->concat(['']),
                'group1' => 'groupnum',
                'cohort1' => $data->func()->concat([
                    'grade_level' => 'literal',
                    ' Semester'
                ]),
                'idnumber' => 'student_id',
                'auth' => $data->func()->concat(['oauth2']),
                'profile_field_tsmufaculty' => 'Schools.name',
                'profile_field_tsmuspeciality' => 'Specials.name',
                'profile_field_tsmusemester' => 'grade_level',
                'profile_field_tsmugroup' => 'groupnum',
                'profile_field_tsmuasumkrid' => 'asumkr_id',
                'profile_field_tsmucontingentid' => 'c_stud_id'
            ])->contain([           //! For loading associations!
                'Schools' => [
                    'fields' => ['Schools.name']
                ],
                'Specials' => [
                    'fields' => ['Specials.name']
                ]                
            ]);
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
        $this->set('title','Export for Deanery');
        $this->render('moodle');
    }

    public function photos(){
        if ($this->request->is('post')) {
            $zip = new ZipArchive();
            $filename = ROOT.DS."webroot".DS."files/temp_archive/photos.zip";
            unlink($filename);
            if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
                exit("cannot open <$filename>\n");
            }
            $directory = realpath('photo/');
            $options = array('add_path' => 'photos/', 'remove_path' => $directory);
            $zip->addPattern('/\.(?:jpg|jpeg)$/', $directory, $options);
            $zip->close();
            $this->redirect($_SERVER['domain']."/files/temp_archive/photos.zip");
        }
    }

    public function lecturio(){
        $this->set('title','Export for Lecturio');
        if ($this->request->is('post')) {
            switch ($this->request->data['grade_level']) {
                case '1':
                    $grade_level= " AND grade_level IN (1,2)";
                    break;
                case '2':
                    $grade_level= " AND grade_level IN (3,4)";
                    break;
                case '3':
                    $grade_level= " AND grade_level IN (5,6)";
                    break;
                case '4':
                    $grade_level= " AND grade_level IN (7,8)";
                    break;
                case '5':
                    $grade_level= " AND grade_level IN (9,10)";
                    break;
                case '6':
                    $grade_level= " AND grade_level IN (11,12)";
                    break;
                default:
                    $grade_level= "";
            }
            $Csv = new CsvComponent($this->options);
            $data = $this->Students->find()->contain(['Schools', 'Specials'])->where([ //! For loading associations!
                        ' Students.school_id = '.$this->request->data['school_id'].
                        ' AND status_id = '.$this->request->data['status_id'].$grade_level]);
            //$data= $data->contain(['Schools', 'Specials']); //! For loading associations!
            $data= $data->select([
                'first_name'=>'first_name',
                'last_name'=>'last_name',
                'email' => $data->func()->concat([
                    'user_name' => 'literal',
                    '@',
                    'tdmu.edu.ua'
                ]),
                'internal_id'=>'student_id',
                'supervisor_email'=>'""',
                'gender'=>'""',
                'date_of_birth'=>'""',
                'group_title' => $data->func()->concat([
                    'Specials.name' => 'literal',
                    ', ',
                    'Schools.name' => 'literal',
                    ', Students'
                ]),
                'role'=>'""',
                'region'=>'""',
                'city'=>'""',
                'access_until_date'=>'""',
                '"1.5x_time_accommodation"'=>'""',
                'phone_number'=>'""',
                'department' => 'Schools.name',
                'efn'=>'""',
            ])->contain([           //! For loading associations!
                'Schools' => [
                    'fields' => ['Schools.name']
                ],
                'Specials' => [
                    'fields' => ['Specials.name']
                ]                
            ]);
            $data =json_decode(json_encode($data), true);//var_dump($data);die();
            if (count($data)>0){
                $Csv->exportCsv(ROOT.DS."webroot".DS."files/usr_".$_SESSION['Auth']['User']['id']."-sch_".$this->request->data['school_id'].".csv", array($data), $this->options);
                return $this->redirect($_SERVER['domain']."/files/usr_".$_SESSION['Auth']['User']['id']."-sch_".$this->request->data['school_id'].".csv");
            }else{
                $this->Flash->error(__('No users'));
            }

        }
    }

}