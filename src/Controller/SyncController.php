<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Network\Request;
use Cake\Network\Http\Client;;

include_once('Firebird/class_firebird.php');
include_once('Firebird/class_firebird_asu_mkr.php');
include_once('Component/Google_Api/autoload.php');
include_once ('Component/Google_Api/src/Google/Client.php');
include_once ('Component/Google_Api/src/Google/Sevice/Oauth2.php');
include_once ('Component/Google_Api/src/Google/Auth/AssertionCredentials.php');
include_once('Component/CsvComponent.php');

use class_ibase_fb;
use class_ibase_fb_asu_mkr;
use Google_Client;
use Google_Auth_AssertionCredentials;
use Google_Service_Directory;
use Google_Service_Oauth2;
use CsvComponent;
use Cake\Network\Email\Email;
use Cake\Datasource\ConnectionManager;

/**
 * Students Controller
 *
 * @property \App\Model\Table\StudentsTable $Students
 */
class SyncController extends AppController
{
    private static $ukrainianToEnglishRules = [
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Ґ' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Є' => 'E',
        'Ж' => 'J',
        'З' => 'Z',
        'И' => 'Y',
        'І' => 'I',
        'Ї' => 'Yi',
        'Й' => 'J',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'Ts',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Shch',
        'Ь' => '',
        'Ю' => 'Yu',
        'Я' => 'Ya',
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'ґ' => 'g',
        'д' => 'd',
        'е' => 'e',
        'є' => 'e',
        'ж' => 'j',
        'з' => 'z',
        'и' => 'y',
        'і' => 'i',
        'ї' => 'yi',
        'й' => 'j',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ь'  => '',
        'ю' => 'yu',
        'я' => 'ya',
        '\'' => ''
    ];

    private $options_csv;

    private $max;

    private $user_for_Api =  "admin4eg@tdmu.edu.ua";

    private $service_account_name = '943473990893-gkf9eek54q9ij5oh0nm1e77487fdd8n4@developer.gserviceaccount.com';

    var $uses = array('Students');

    private $contingent; //object for connect with contingent

    private $students;

    private $speciality;

    private $status = false;

    private $message = array();

    private $options = array();

    private $client;

    private $service;

    private $test;


    public function beforeFilter(){  // Constructor
        $this->Auth->allow('api');
        $this->contingent = new class_ibase_fb();
        $this->contingent->sql_connect();
        $this->asu_mkr = new class_ibase_fb_asu_mkr(); //connect to ASU_MKR
        $this->asu_mkr->sql_connect();        
        $this->options_csv = [
            'length' => 0,
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => '\\',
            'headers' => true,
            'text' => false,
        ];
    }

    public function index(){
    
        return $this->redirect(['action' => 'contingent']);
        
        $uploadData = '';
var_dump($this->request);
var_dump($this->request->data(['special']));
        if ($this->request->is('post')) {
var_dump($this->request->data['file']['name']);        
            if(!empty($this->request->data['file']['name'])){
                $fileName = $this->request->data['file']['name'];
                $uploadPath = 'files/teachers/';
                $uploadFile = $uploadPath.$fileName;
                if(move_uploaded_file($this->request->data['file']['tmp_name'],$uploadFile)){
                    $this->message[]['message']="File has been uploaded";
                    //$uploadData = $this->Files->newEntity();
                    //$uploadData->name = $fileName;
                    //$uploadData->path = $uploadPath;
                    //$uploadData->created = date("Y-m-d H:i:s");
                    //$uploadData->modified = date("Y-m-d H:i:s");
                    //if ($this->Files->save($uploadData)) {
                    //    $this->Flash->success(__('File has been uploaded and inserted successfully.'));
                    //}else{
                    //    $this->Flash->error(__('Unable to upload file, please try again.'));
                    //}
                }else{
                    $this->Flash->error(__('Unable to upload file, please try again.'));
                }
            }else{
                //$this->Flash->error(__('Please choose a file to upload.'));
                return $this->redirect(['action' => 'contingent']);
            }
            
        }
        //$this->set('uploadData', $uploadData);
        
        //$files = $this->Files->find('all', ['order' => ['Files.created' => 'DESC']]);
        //$filesRowNum = $files->count();
        //$this->set('files',$files);
        //$this->set('filesRowNum',$filesRowNum);     
        
    //    return $this->redirect(['action' => 'contingent']);
    }

    private function _get_students(){
        $this->students = $this->contingent->gets("
			SELECT STUDENTS.DEPARTMENTID,STUDENTS.SEMESTER,STUDENTS.FIO,STUDENTS.NFIO,STUDENTS.STUDENTID,STUDENTS.PHOTO,STUDENTS.ARCHIVE,STUDENTS.GROUPNUM,STUDENTS.STATUS,STUDENTS.SPECIALITYID,STUDENTS.IDCODE 
			FROM STUDENTS WHERE ARCHIVE=0");
    }
    private function _get_speciality(){
        $this->speciality = $this->contingent->gets("
			SELECT SPECIALITYID,SPECIALITY,CODE FROM GUIDE_SPECIALITY WHERE USE=1");
    }

    private function _test_ping(){
        $this->test = $this->contingent->gets("
			SELECT First 1 STUDENTID
			FROM STUDENTS WHERE ARCHIVE=0");
        if (!isset($this->test[1]['STUDENTID'])) $this->Flash->error('Connect to Contingent not found!!!');
    }
    
    /*
     *
     * function for connect with directory Api Google
     *
     */
    private function connect_google_api(){
        $this->client = new Google_Client();
        $this->client->setApplicationName("SysAdminka");
        $key = (file_get_contents(ROOT.DS."webroot".DS."Google_key".DS."1fa047635e4bac618edbe30d56e074cff7ad9a75-privatekey.p12"));
        $this->service = new Google_Service_Directory($this->client);
        if (isset($_SESSION['service_token'])) {
            $this->client->setAccessToken($_SESSION['service_token']);
        }
        $cred = new Google_Auth_AssertionCredentials(
            $this->service_account_name,
            array('https://www.googleapis.com/auth/admin.directory.user'),
            $key,
            'notasecret'
        );
        $cred->sub = $this->user_for_Api;
        $this->client->setAssertionCredentials($cred);
        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion($cred);
        }
        $_SESSION['service_token'] = $this->client->getAccessToken();
    }

    /*
     *
     *  for Service google
     *
     */
    public function LDB_ToGoogle_photo($user,$force=NULL){
        $this->connect_google_api();
        $datas = new \Google_Service_Directory_UserPhoto();
            $user_of_google = $this->service->
                users->
                listUsers(['orderBy'=>'email',
                           'domain'=>'tdmu.edu.ua',
                           'query'=>'email='.$user.'@tdmu.edu.ua'])
                ->getUsers();
            if(count($user_of_google)>0){

//                $this->service->users_photos->delete($user.'@tdmu.edu.ua');
                try {
                    $this->service->users_photos->get($user.'@tdmu.edu.ua');
                } catch (\Exception $e) {
                    $force=true;
                }
                if ($force==true){
                    $datas->setPhotoData($this->base64url_encode(file_get_contents(ROOT.DS."webroot".DS."photo".DS.$user.".jpg")));
                    $datas->setWidth(124);
                    $this->service->users_photos->update($user.'@tdmu.edu.ua',$datas);
                    echo "Ok";
                }
            }
        $this->layout='ajax';
        $this->autoRender = false;
    }
        /*
         *
         *  Delete photo in google
         *
         */
    public function LDB_ToGoogle_photo_delete($user){
        $this->connect_google_api();
        $user_of_google = $this->service->
            users->
            listUsers(['orderBy'=>'email',
                       'domain'=>'tdmu.edu.ua',
                       'query'=>'email='.$user.'@tdmu.edu.ua'])
            ->getUsers();
        if(count($user_of_google)>0){
            $this->service->users_photos->delete($user.'@tdmu.edu.ua');
            echo "Ok";
        }
        $this->layout='ajax';
        $this->autoRender = false;
    }
        /*
         *
         *  Get all information the student with google
         *
         */
    public function Get_info_google($user){
        $this->connect_google_api();
        $user_of_google = $this->service->users->get($user.'@tdmu.edu.ua');
        $user_of_google->setName = $user_of_google->name;
        echo json_encode($user_of_google);
        $this->layout='ajax';
        $this->autoRender = false;
    }

    private function base64url_encode($mime) {
        return rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
    }

    public function contingent(){
    $this->_test_ping();
    $this->_test_ping_asu_mkr();
        if ($this->request->is('post')) {
            if ($this->request->data(['special'])==on){
                $this->_get_speciality();
                $this->_sync_C_with_LDB_spec();
            }
            if ($this->request->data['archive']==on){
                $this->_sync_archive();
            }
            if ($this->request->data(['all_students'])==on){
                $this->options['new_student'] = 0;
                $this->options['new_student_failed'] = 0;
                $this->options['clone_login_in students'] = 0;
                
                $this->_get_students();
                $this->_sync_C_with_LDB_users();
            }
            
            //----------ASU MKR actions begin------------------
            if ($this->request->data(['specials_asumkr'])==on){
                $this->_get_speciality_asu_mkr();
                $this->_sync_ASU_with_LDB_spec();
            }
            if ($this->request->data(['all_students_asumkr'])==on){
                 $this->options['rename_student']=0;
                 $this->options['new_student']=0;
                 
                 $this->_get_students_asu_mkr();
                 $this->_sync_ASU_with_LDB_users();
            }
            if ($this->request->data(['init_ldb_dbstructure_upgrade'])==on){
                 $this->_initial_LDB_dbstructure_upgrade();
            }
            if ($this->request->data(['ldb_names_cleanup'])==on){
                 $this->_LDB_names_cleanup();
            }            
            if ($this->request->data(['init_all_affiliation_asumkr'])==on){
                 $this->_initial_update_ldb_affiliation_ids();
            }            
            if ($this->request->data(['init_all_students_asumkr'])==on){
                 $this->_get_students_asu_mkr();
                 $this->_initial_update_ldb_students_ids();
            }
            if ($this->request->data['photo_asumkr']==on){
                $this->_get_students_asu_mkr();
                $this->_sync_ASU_with_LDB_photo();
            }
            if ($this->request->data['init_asumkr_portal_users']==on){
                $this->_initial_update_asumkr_portal_userdata();
            }
            if ($this->request->data['fix_asumkr_portal_useremails']==on){
                $this->_fix_asumkr_portal_useremails();
            }
            //----------ASU MKR actions end------------------
            
            if ($this->request->data['photo']==on){
                $this->_get_students();
                $this->_sync_C_with_LDB_photo();
            }
            if ($this->request->data['google_photo']==on){
                $this->set('modal_google',true);
            }
            if ($this->request->data['cron_google_send']==on){
                $output = shell_exec('sudo -u gaps /opt/gasync/run_google_sync.sh');
                $logs = 'View log sync with Google <a href="'.$_SERVER['domain'].'/log/SDS_sync.log">View</a><br/><br/>';
                $this->message[]['message']=$logs.$output;
            }

            if(!empty($this->request->data['file']['name'])){
                $fileName = $this->request->data['file']['name'];
                $uploadPath = ROOT.DS."webroot".DS."files/teachers/";
                $uploadFile = $uploadPath.$fileName;
                $uploadFileExt=strtolower(end(explode('.',$_FILES['file']['name'])));
                $expensions= array("csv");
                
                if(in_array($uploadFileExt,$expensions)=== false) {
                    $this->Flash->error(__('Extension not allowed, please choose a CSV file.'));
                } else {
                    if(move_uploaded_file($this->request->data['file']['tmp_name'],$uploadFile)){
                        //$this->message[]['message']="File has been uploaded";
                        $this->_initial_update_asumkr_portal_teacherdata($uploadFile);
                    }else{
                        $this->Flash->error(__('Unable to upload file, please try again.'));
                    }
                }
            }
            
            if ($this->status==true){
                $this->loadModel('Synchronized');
                $data = $this->Synchronized->newEntity();
                $data['status_contingent']='ok';
                $data['status_google']='--';
                $data['statistics']=json_encode($this->options);
                $data['date']=mktime();
                if ($this->Synchronized->save($data)) {
                    $this->message[]['message']='Sync is Ok. DB write status Ok. New students: '.$this->options['new_student'].' (FAILED: '.$this->options['new_student_failed'].'), renamed students:'.$this->options['rename_student'];
                }
            }
            $this->Flash->error_form($this->message);
        }
        $this->render('index');
    }

    private function _sync_archive(){
        $this->loadModel('Students');
        //$students = $this->Students->find()->where(['((grade_level > 9) OR ((grade_level IN (1,2,3)) AND (school_id=44)))']);
        $students = $this->Students->find()->all();
        foreach($students as $student){
            $student_of_contingent = $this->contingent->gets("SELECT STUDENTS.ARCHIVE FROM STUDENTS WHERE STUDENTID LIKE '".$student->student_id."'");
            if ($student_of_contingent[1]['ARCHIVE']==1){
                $data = $this->Students->get($student->id);
                $data['status_id']=10;
                if ($this->Students->save($data)) {
                    $this->status=true;
                    $this->options['students_arhive']++;
                }
            }
        }
        if($this->options['students_arhive']==0){
            $this->message[]['message']="Sorry, there are no new records in Contingent databace";
        }else{
            $this->message[]['message']='Students is in archive: '. $this->options['students_arhive'];
        }
    }

    private function _view_photo_blob($photo){
        header("Content-Type: image/jpeg");
        ibase_blob_echo($photo);
    }

    private function _sync_C_with_LDB_photo(){
        $this->loadModel('Students');
        foreach($this->students as $student_of_contingent){
            $student_ldb = $this->Students->find()
                ->where(['student_id ' => $student_of_contingent['STUDENTID']],['status_id'=>1])
                ->first();
            $img = ibase_blob_get(ibase_blob_open($student_of_contingent['PHOTO']), ibase_blob_info($student_of_contingent['PHOTO'])[0]);
            file_put_contents('photo/'.$student_ldb['user_name'].'.jpg', $img);
        }
        $this->message[]['message']='Sync photos was successful';

    }

//-----------------------------------------------------------------------------------------------------------------------
    public function api(){
        $this->_get_students();
        $this->_sync_C_with_LDB_users();
        //TODO: replacement for ASU MKR:
        //$this->_get_students_asu_mkr();
        //$this->_sync_ASU_with_LDB_users();
        if ($this->status==true){
            $this->loadModel('Synchronized');
            $data = $this->Synchronized->newEntity();
            $data['status_contingent']='ok';
            $data['status_google']='--';
            $data['statistics']=json_encode($this->options);
            $data['date']=mktime();
            if ($this->Synchronized->save($data)) {
                $output = shell_exec('sudo -u gaps /opt/gasync/run_google_sync.sh');
            }
        }
        $this->layout = 'ajax';
        $this->render(false);
    }
//-----------------------------------------------------------------------------------------------------------------------
    /*
     * Sync Contingent with Local DataBase
     */
    private function _sync_C_with_LDB_spec(){
        $this->loadModel('Specials');
        foreach($this->speciality as $speciality_of_contingent){
            $specials_ldb = $this->Specials->find()
                ->where(['special_id ' => $speciality_of_contingent['SPECIALITYID'].' AND status_id != 7'])
                ->first();
                if (isset($specials_ldb)){
                    $rename=0;
                    $data = $this->Specials->find()->where(['special_id'=>$specials_ldb->special_id])->first();
                    if ($speciality_of_contingent['SPECIALITYID']!=$specials_ldb->special_id){
                        $rename++;
                        $data['special_id']=$speciality_of_contingent['SPECIALITYID'];
                    }
                    if ($speciality_of_contingent['SPECIALITY'].' ('.$speciality_of_contingent['CODE'].')'!=$specials_ldb->name){
                        $rename++;
                        $data['name']=$speciality_of_contingent['SPECIALITY'].' ('.$speciality_of_contingent['CODE'].')';
                    }
                    if ($speciality_of_contingent['CODE']!=$specials_ldb->code){
                        $rename++;
                        $data['code']=$speciality_of_contingent['CODE'];
                    }
                    if($rename>0){
                        if ($this->Specials->save($data)) {
                            $this->options['rename_specials']++;
                            $this->status=true;
//                            $this->message[]['message']='Editing speciality: '.$this->options['rename_specials'];
                        }
                    }
                }else{
                    $data = $this->Specials->newEntity();
                    $data['special_id'] = $speciality_of_contingent['SPECIALITYID'];
                    $data['name'] = $speciality_of_contingent['SPECIALITY'];
                    $data['code'] = $speciality_of_contingent['CODE'];
                    if ($this->Specials->save($data)) {
                        $this->options['new_specials']++;
                        $this->status=true;
//                        $this->message[]['message']='New speciality: '.$this->options['new_specials'];

                    }
                }
        }
        if(($this->options['rename_specials']==0) and ($this->options['new_specials']==0)){
            $this->message[]['message']="Sorry, there are no new records in Contingent databace";
        }
    }



    /*
     * Sync Students with Contingent into Local DataBase
     */
    private function _sync_C_with_LDB_users(){
        $this->loadModel('Students');
        $this->_max_id();
        foreach($this->students as $student_of_contingent){
            $student_ldb = $this->Students->find()
                ->where(['student_id' => $student_of_contingent['STUDENTID']])
                ->first();
            if ($student_of_contingent['STATUS']=='С'){
//var_dump($student_of_contingent['STUDENTID']);
                if (isset($student_ldb)){
                    $rename=0;
                    $student_of_contingent['NFIO']!=null ? $name = $this->_emplode_fi($student_of_contingent['NFIO']) : $name = $this->_emplode_fi($student_of_contingent['FIO']);
//var_dump($student_of_contingent['STUDENTID']);
                    $data = $this->Students->get($student_ldb->id);

//var_dump($student_ldb);
                    if ($student_of_contingent['DEPARTMENTID']!=$student_ldb->school_id){
                        $rename++;
//var_dump('rename-dep-CONT='.$student_of_contingent['DEPARTMENTID']);
//var_dump('rename-local='.$student_ldb->school_id);
                        $data['school_id']=$student_of_contingent['DEPARTMENTID'];
                    }
                    if ($student_of_contingent['SPECIALITYID']!=$student_ldb->special_id){
                        $rename++;
//var_dump('rename-spec-CONT='.$student_of_contingent['SPECIALITYID']);
//var_dump('rename-local='.$student_ldb->special_id);
                        $data['special_id']=$student_of_contingent['SPECIALITYID'];
                    }
                    if ($student_of_contingent['SEMESTER']!=$student_ldb->grade_level){
                        $rename++;
//var_dump('rename-semestr-CONT='.$student_of_contingent['SEMESTER']);
//var_dump('rename-local='.$student_ldb->grade_level);
                        $data['grade_level']=$student_of_contingent['SEMESTER'];
                    }
                    if ($student_of_contingent['GROUPNUM']!=$student_ldb->groupnum){
                        $rename++;
//var_dump('rename-grp-CONT='.$student_of_contingent['GROUPNUM']);
//var_dump('rename-local='.$student_ldb->groupnum);
                        $data['groupnum']=$student_of_contingent['GROUPNUM'];
                    }
                    if(isset($student_of_contingent['IDCODE'])){
                        if ($student_of_contingent['IDCODE']!=$student_ldb->ipn_id){
                            $rename++;
//var_dump('rename-idcode-CONT='.$student_of_contingent['IDCODE']);
//var_dump('rename-local='.$student_ldb->ipn_id);
                            $data['ipn_id']=$student_of_contingent['IDCODE'];
                        }
                    }
                    if ($name['fname']!=$student_ldb->first_name){
                        $rename++;
//var_dump('rename-fname-CONT='.$name['fname']);
//var_dump('rename-local='.$student_ldb->first_name);
                        $data['first_name']=$name['fname'];
                    }
                    if ($name['lname']!=$student_ldb->last_name){
                        $rename++;
//var_dump('rename-lname-CONT='.$name['lname']);
//var_dump('rename-local='.$student_ldb->last_name);
                        $data['last_name']=$name['lname'];
                    }
                    if ($student_of_contingent['ARCHIVE']==1 and $student_ldb->status_id!=10){
                        $rename++;
//var_dump('rename-arch-CONT='.$student_of_contingent['ARCHIVE']);
//var_dump('rename-local='.$student_ldb->status_id);
                        $data['status_id'] = 10;
                        $this->options['archive_student']++;
                    }else if ($student_of_contingent['ARCHIVE']==0 and $student_ldb->status_id==10){
                        $rename++;
//var_dump('rename-arch-CONT='.$student_of_contingent['ARCHIVE']);
//var_dump('rename-local='.$student_ldb->status_id);
                        $data['status_id'] = 1;
                    }
                        if($rename>0){

                            if ($this->Students->save($data)) {
                                $this->options['rename_student']++;
                                $this->status=true;
//                                $this->message[]['message']='Editing students: '.$this->options['rename_student'];
                            }
                        }


                }else{
//var_dump('Create new record: '.$student_of_contingent['STUDENTID'].' - '.$student_of_contingent['FIO'].' - '.$student_of_contingent['NFIO']);
                    $student_of_contingent['NFIO']!=null ? $name = $this->_emplode_fi($student_of_contingent['NFIO']) : $name = $this->_emplode_fi($student_of_contingent['FIO']);
                    
                    $name['uname'] = $this->create_Google_username($name);
                    
                    $data = $this->Students->newEntity();
                    $data['student_id'] = $student_of_contingent['STUDENTID'];
                    $data['school_id'] = $student_of_contingent['DEPARTMENTID'];
                    $data['special_id'] = $student_of_contingent['SPECIALITYID'];
                    $data['groupnum'] = $student_of_contingent['GROUPNUM'];
                    $data['first_name'] = $name['fname'];
                    $data['last_name'] = $name['lname'];
//var_dump('will create = '.$data['last_name']);                    
                    $data['user_name'] = $name['uname'];
                    $data['grade_level'] = $student_of_contingent['SEMESTER'];
                    $data['send_photo_google'] = 0;
                    $data['password'] = $this->_generate_pass();
                    $student_of_contingent['ARCHIVE']==1 ?  $data['status_id'] = 10 :  $data['status_id'] = 1;

                    $student_login_clone = $this->Students->find()
                        ->where(['user_name' => $name['uname']])
                        ->first();

                    if (isset($student_login_clone)){
                        $data['status_id'] = 3;
                        $this->options['clone_login_in students']++;
                    }
                    if(isset($student_of_contingent['IDCODE'])){
                        $data['ipn_id']=$student_of_contingent['IDCODE'];
                    }
//var_dump($data);
                    if ($this->Students->save($data)) {
                        $new_student_for_email++;
                        $this->options['new_student']++;
                        $this->status=true;
                        //$this->message[]['message']='New students: '.$this->options['new_student'];
                    } else {
                            $this->options['new_student_failed']++;
                            //debug($data->errors());
//var_dump('failed! - '.$student_of_contingent['STUDENTID']);
                    }
                }
            } else {
                if (isset($student_ldb)){
                    $rename=0;
                    $data = $this->Students->get($student_ldb->id);
                    if ($student_of_contingent['ARCHIVE']==1 and $student_ldb->status_id!=10){
                        $rename++;
                        $data['status_id'] = 10;
                        $this->options['archive_student']++;
                    }else if ($student_of_contingent['ARCHIVE']==0 and $student_ldb->status_id==10){
                        $rename++;
                        $data['status_id'] = 1;
                    }
                    if($rename>0){
                        if ($this->Students->save($data)) {
                            $this->options['rename_student']++;
                            $this->status=true;
                            //$this->message[]['message']='Editing archive students: '.$this->options['rename_student'];
                        } else {
                            $this->options['new_student_failed']++;
                            //debug($data->errors());
                        }
                    }                
                }
            }
        }
        if(($this->options['rename_student']==0) and ($this->options['new_student']==0)){
            $this->message[]['message']="Sorry, there are no new records in Contingent database";
        }
        if (count($new_student_for_email)>0){
            $this->send_email($new_student_for_email,"There are ".$this->options['new_student']." new and ".$this->options['rename_student']." renamed students in SysAdmin!");
        }
    }

    private function _max_id(){
        $this->loadModel('Students');
        $this->max = $this->Students->find('all', array('order'=>'Students.id DESC'))->first();
    }

    /*
     *
     *  Send email SMTP
     *
     */

    private function send_email($new_student_for_email,$title){
        $this->loadModel('Synchronized');

        $Csv = new CsvComponent($this->options_csv);
        if (isset($this->max->id)){
            $data = $this->Students->find()->where(['id >'.$this->max->id])->all();
        }else{
            $data = $this->Students->find()->all();
        }
        $data =json_decode(json_encode($data), true);
        $Csv->exportCsv(ROOT.DS."webroot".DS."files/emails/email.csv", array($data), $this->options_csv);
        $email = new Email();
        $email->transport('gmail');

        $email->from([$this->Settings->__find_setting('admin_emails',$this->Settings->_get_settings()) => 'Admilka(TDMU)'])
            ->to(json_decode($this->Settings->__find_setting('admin_emails_for_send',$this->Settings->_get_settings())))
            ->subject($title)
            ->attachments([ROOT.DS."webroot".DS."files/emails/email.csv"])
            ->send($title);
    }


    /*
     * create user name
     */
    private function _create_username($ukrainianText){
            $transliteratedText = '';
            if (mb_strlen($ukrainianText) > 0) {
                $transliteratedText = str_replace(
                    array_keys(self::$ukrainianToEnglishRules),
                    array_values(self::$ukrainianToEnglishRules),
                    $ukrainianText
                );
            }
            return strtolower($transliteratedText);
            //TODO: introduce
            //return transliterator_transliterate ('Any-Latin; [\u0100-\u7fff] Remove; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();', $ukrainianText);
    }


    /*
     * generate pass
     */
    private function _generate_pass(){
        //return rand(10000000,99999999);
        return intval(88888888);
    }

    /*
     * implode fio -> fname, lname
     */
    private function _emplode_fi($str){
        $str = trim($str); //Remove all leading and trailing spaces 
        $str = str_replace("(","",$str);
        $str = str_replace(")","",$str);
        $str = str_replace("-","",$str);
        $str = str_replace("'","",$str);
        $str = str_replace(":","",$str);
        $str = str_replace(".","",$str);
        $str = str_replace("`","",$str);
        $str = str_replace("’","",$str);
        $str = str_replace("\"","",$str);

        $fullname = explode(" ", $str);

        //required to be in sync with ASU procedure: fix empty names
        $name['lname']=$fullname[0];
        $name['firstname']=$fullname[1];
        $name['middlename']=$fullname[2];
        //ASU: Set Fname or Mname in place of LastName - if it not exist - to meet Google requirements
        if (mb_strlen($name['lname'])<2) {
            if (mb_strlen($name['firstname'])>2){
                $name['lname'] = $name['firstname'];
            } elseif (mb_strlen($name['middlename'])>2) {
                $name['lname'] = $name['middlename'];
            } else {
                $name['lname'] = 'noLN';
            }
//var_dump($name);
        }
        if (mb_strlen($name['firstname'])<2) {
            if (mb_strlen($name['lname'])>2){
                $name['firstname'] = mb_substr($name['lname'],0,3);
            } elseif (mb_strlen($name['middlename'])>2) {
                $name['firstname'] = mb_substr($name['middlename'],0,3);
            } else {
                $name['firstname'] = 'nFN';
            }
//var_dump($name);
        }
        
        if (isset($name['middlename'])) {
            $name['fname'] = $name['firstname'] . " " . $name['middlename'];
            $tmpfmn = mb_substr($name['firstname'],0,3).mb_substr($name['middlename'],0,3);
        } else {
            $name['fname'] = $name['firstname'];
            $tmpfmn = mb_substr($name['firstname'],0,3).mb_substr($name['lname'],0,3);
        }

        $name['uname'] = $this->_create_username($name['lname'])."_".$this->_create_username($tmpfmn);
        $name['uname'] = str_replace(" ","",$name['uname']); //ASU: finally - remove all possible ocasional spaces

        //check if such username could exist
        do {
            $tmp_student_ldb = $this->Students->find()
                ->where(['user_name' => $name['uname']])
                ->first();
            if (!empty($tmp_student_ldb)) {
                $name['uname'] = $name['uname'].'1'; 
            }
        } while (!empty($tmp_student_ldb));
//var_dump($name);
        return $name;
    }
    
//==============================ASU MKR===============================================
    /*
     * Check ASU MKR database connect
     */
    private function _test_ping_asu_mkr(){
        $this->test_mkr = $this->asu_mkr->gets("
			SELECT First 1 ST.ST1 AS STUDENTID
			FROM ST inner join std on (st.st1 = std.std2) WHERE (st.st1>0)AND(std7 is null)AND((STD11<>2)OR(STD11<>4))
            ");
        if (!isset($this->test_mkr[1]['STUDENTID'])) $this->Flash->error('Connect to ASU MKR not found!!!');
    }
    
    /*
     * Get specialities from ASU MKR
     * SP_ID, PNSP_ID
     */
    private function _get_speciality_asu_mkr(){
        $this->speciality_mkr = $this->asu_mkr->gets("
            SELECT SP.SP1 AS SP_ID, PNSP.PNSP1 AS PNSP_ID, PNSP.PNSP2 AS SPECIALITY, SP.SP2 AS SPECIALITY2, SP.SP4 AS CODE, SP.SP14 AS LEVEL FROM SP left join PNSP ON (PNSP.PNSP1=SP.SP11) WHERE  SP.SP1>0
            ");          
    }
    
    /*
     * Get students form ASU MKR DataBase
     */
    private function _get_students_asu_mkr(){
    // TODO: get all students and check status (std.std7 and std.std11) on sync?????!!!!!!!
        $this->students_mkr= $this->asu_mkr->gets("
select 
    f.f1,
    st.st1,
    st.st2,
    st.st3,
    st.st4,
    st.st15,
    st.st32,
    st.st71, 
    st.st74, 
    st.st75, 
    st.st76, 
    st.st144,
    st.st108,
    gr.gr3,
    std.std7,
    std.std11,
    pnsp.pnsp1,
    sp.sp1
from st
   inner join std on (st.st1 = std.std2)
   inner join gr on (std.std3 = gr.gr1)
   inner join sg on (gr.gr2 = sg.sg1)
   inner join sp on (sg.sg2 = sp.sp1)
   inner join pnsp on (sp.sp11 = pnsp.pnsp1)
   inner join f on (sp.sp5 = f.f1)
where 
   (std.std7 is null ) and (std.std11 <> 1) and (st.st2<>'');
            ");
    }
    
    /*
     * Get teachers form ASU MKR DataBase (with IPN!)
     * IPN - p7 or p13??
     */
    private function _get_teachers_asu_mkr($ipn){
        $this->teachers_mkr= $this->asu_mkr->gets("
SELECT 
    p.p1,
    p.p3,
    p.p4,
    p.p5,
    p.p7
FROM p 
WHERE
    p.p7='".$ipn."'   
    ");    
    }
    
    private function _get_asu_mkr_portal_user($username, $usertype=0){
        unset($this->asu_mkr_portal_users);
        $this->asu_mkr_portal_users = $this->asu_mkr->gets("
            SELECT u1 FROM users WHERE u2='".$username."' AND u5=".$usertype."
        ");
    }
    
    private function _get_asu_mkr_portal_user_by_id($userid, $usertype=0){
        unset($this->asu_mkr_portal_users);
        $this->asu_mkr_portal_users = $this->asu_mkr->gets("
            SELECT u1, u4 FROM users WHERE u6='".$userid."' AND u5=".$usertype."
        ");
    }
    
    private function _get_asu_mkr_portal_user_by_email($useremail, $usertype=0){
        unset($this->asu_mkr_portal_users_email);
        $this->asu_mkr_portal_users_email = $this->asu_mkr->gets("
            SELECT u1, u4 FROM users WHERE u4='".$useremail."' AND u5=".$usertype."
        ");
    }
    /*
     * Sync ASU MKR specialities with Local DataBase
     * SP_ID, PNSP_ID
     */
    private function _sync_ASU_with_LDB_spec(){
        $this->loadModel('Specials');
        foreach($this->speciality_mkr as $speciality_of_asu_mkr){
            $specials_ldb = $this->Specials->find()
                ->where(['pnsp_id ' => $speciality_of_asu_mkr['PNSP_ID']])
                ->where(['sp_id ' => $speciality_of_asu_mkr['SP_ID']])
                ->first();

                if (isset($specials_ldb)){
                    $rename=0;
                    $data = $this->Specials->find()->where(['pnsp_id'=>$specials_ldb->pnsp_id, 'sp_id ' => $specials_ldb->sp_id])->first();
                    //use complex name
                    if ($speciality_of_asu_mkr['SPECIALITY']." (".$speciality_of_asu_mkr['SPECIALITY2']." ".$speciality_of_asu_mkr['CODE'].")" != $specials_ldb->name){
                        $rename++;
                        $data['name']=$speciality_of_asu_mkr['SPECIALITY']." (".$speciality_of_asu_mkr['SPECIALITY2']." ".$speciality_of_asu_mkr['CODE'].")";
                    }
                    if ($speciality_of_asu_mkr['CODE']!=$specials_ldb->code){
                        $rename++;
                        $data['code']=$speciality_of_asu_mkr['CODE'];
                    }
                    if($rename>0){
                        if ($this->Specials->save($data)) {
                            $this->options['rename_specials']++;
                            $this->status=true;
//                            $this->message[]['message']='Editing speciality: '.$this->options['rename_specials'];
                        }
                    }
                }else{
                    $data = $this->Specials->newEntity();
                    $data['special_id'] = $speciality_of_asu_mkr['SP_ID'];
                    $data['pnsp_id'] = $speciality_of_asu_mkr['PNSP_ID'];
                    $data['sp_id'] = $speciality_of_asu_mkr['SP_ID'];
                    $data['name'] = $speciality_of_asu_mkr['SPECIALITY']." (".$speciality_of_asu_mkr['SPECIALITY2']." ".$speciality_of_asu_mkr['CODE'].")";
                    $data['code'] = $speciality_of_asu_mkr['CODE'];
                    if ($this->Specials->save($data)) {
                        $this->options['new_specials']++;
                        $this->status=true;
//                        $this->message[]['message']='New speciality: '.$this->options['new_specials'];

                    }
                }
        }
        if(($this->options['rename_specials']==0) and ($this->options['new_specials']==0)){
            $this->message[]['message']="Sorry, there are no new records in ASU MKR database";
        } else {
            $this->message[]['message']="There are ".$this->options['new_specials']." new and ".$this->options['rename_specials']." renamed  speciality records in LDB";
        }
    }
    
    private function _update_student_record_by_ASU($data, $student_of_asu_mkr, $student_ldb, $name){
        $this->loadModel('Schools');
        $rename=0;
                    //update existing student's record
                    //$data = $this->Students->get($student_ldb->id);
                    //if ($student_of_asu_mkr['ST1']!=$student_ldb->asumkr_id){ //should be executed first time only
                    //    $rename++;
                    //    $data['asumkr_id']=$student_of_asu_mkr['ST1'];
                    //}
                    if ($student_of_asu_mkr['ST1']!=$student_ldb->student_id){ //should be executed first time only
                        $rename++;
                        $data['student_id']=$student_of_asu_mkr['ST1'];
                    }
                    if ($student_of_asu_mkr['F1']!=$student_ldb->f_id){
                        $rename++;
                        $data['f_id']=$student_of_asu_mkr['F1'];
                        //use old Contingent id for gsync
                        $school = $this->Schools->find()
                            ->where(['f_id' => $student_of_asu_mkr['F1']])
                            ->first();
                        if ($school) {
                            $data['school_id'] = $school->school_id;   //for gsync
                        }
                    }
                    if ($student_of_asu_mkr['PNSP1']!=$student_ldb->pnsp_id){
                        $rename++;
                        $data['pnsp_id']=$student_of_asu_mkr['PNSP1'];
                    }
                    if ($student_of_asu_mkr['SP1']!=$student_ldb->sp_id){
                        $rename++;
                        $data['sp_id']=$student_of_asu_mkr['SP1'];
                    }
                    if ($student_of_asu_mkr['SP1']!=$student_ldb->special_id){
                        $rename++;
                        $data['special_id']=$student_of_asu_mkr['SP1'];   //for gsync
                    }
                    if ($student_of_asu_mkr['ST71']!=$student_ldb->grade_level){
                        $rename++;
                        $data['grade_level']=$student_of_asu_mkr['ST71'];
                    }
                    if ($student_of_asu_mkr['ST15']!=$student_ldb->ipn_id){
                        $rename++;
                        $data['ipn_id']=$student_of_asu_mkr['ST15'];
                    }
                    if ($student_of_asu_mkr['GR3']!=$student_ldb->groupnum){
                        $rename++;
                        $data['groupnum']=$student_of_asu_mkr['GR3'];
                    }
                    if ($name['fname']!=$student_ldb->first_name){
                        $rename++;
                        $data['first_name']=$name['fname'];
                    }
                    if ($name['lname']!=$student_ldb->last_name){
                        $rename++;
                        $data['last_name']=$name['lname'];
                    }
                    //if ($student_of_asu_mkr['ARCHIVE']==true and $student_ldb->status_id!=10){//TODO: how to get this?
                    //    $rename++;
                    //    $data['status_id'] = 10;
                    //    $this->options['archive_student']++;
                    //}else if ($student_of_asu_mkr['ARCHIVE']==false and $student_ldb->status_id==10){
                    //    $rename++;
                    //    $data['status_id'] = 1;
                    //}
                    
                    if (($student_of_asu_mkr['STD11']==2||$student_of_asu_mkr['STD11']==4)&&($student_ldb->status_id==1)){
                        $data['status_id'] = 10;  //Move student TO archive:
                        $this->options['archive_student']++;
                    } elseif ($student_ldb->status_id==10&&$student_of_asu_mkr['STD11']==0) {
                        $data['status_id'] = 1;    //Get student FROM archive:
                    }        
        return array($rename, $data);
    }
    
    private function _LDB_get_student_by_ASUID($tmp_student_of_asu_mkr, $status=1){
        $this->loadModel('Students');
        // search GAPS Local Database for an existing ACTIVE students in two ways:
        unset($tmp_student_ldb);
        $tmp_student_ldb = $this->Students->find()
            ->where(['asumkr_id' => $tmp_student_of_asu_mkr['ST1'], 'status_id'=>$status])
            ->first();
//debug
//$tmpdupl = array(3,7,11,12);
//if(in_array($status, $tmpdupl)){
//var_dump("sub-search-by-asuid=".$tmp_student_of_asu_mkr['ST1']."-for-status=".$status);
//var_dump($tmp_student_ldb);
//}       
        if (!isset($student_ldb)) {
            if (mb_strlen($tmp_student_of_asu_mkr['ST108'])>1){ // get existing user by Contingent ID
            //TODO: !!!!!!!!!!!!!!!STRONG NECESSARY TO FILL ST108 FIRST!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $tmp_student_ldb = $this->Students->find()
                ->where(['c_stud_id' => $tmp_student_of_asu_mkr['ST108'], 'status_id'=>$status])
                ->first();
//debug
//$tmpdupl = array(3,7,11,12);
//if(in_array($status, $tmpdupl)){
//var_dump("sub-search-by-contid=".$tmp_student_of_asu_mkr['ST108']."-for-status=".$status);
//var_dump($tmp_student_ldb);
//}
                        
            }
        }
        return $tmp_student_ldb;
    }
    
    private function create_Google_username($namearr){
        $tmpname = explode(" ", $namearr['fname']);
        $tmpFname = $this->_create_username(trim($tmpname[0]));
        $tmpMname = $this->_create_username(trim($tmpname[1]));
        $tmpLastName = $this->_create_username(trim($namearr['lname']));
        if (mb_strlen($tmpLastName)<2) {
            if (mb_strlen($tmpFname)>2){
                $tmpLastName = $tmpFname;
            } elseif (mb_strlen($tmpMname)>2) {
                $tmpLastName = $tmpMname;
            } else {
                $tmpLastName = 'nolastname';
            }
        }
        if (mb_strlen($tmpFname)<2) {
            if (mb_strlen($tmpFname)>2){
                $tmpFname = mb_substr($tmpLastName,0,3);
            } elseif (mb_strlen($tmpMname)>2) {
                $tmpFname = mb_substr($tmpMname,0,3);
            } else {
                $tmpFname = 'nfn';
            }
        } else {
            $tmpFname = mb_substr($tmpFname,0,3);
        }
        if (mb_strlen($tmpMname)<2) {
            if (mb_strlen($tmpFname)>2){
                $tmpMname = mb_substr($tmpLastName,0,3);
            } elseif (mb_strlen($tmpFname)>2) {
                $tmpMname = mb_substr($tmpFname,0,3);
            } else {
                $tmpMname = 'nmn';
            }
        } else {
            $tmpMname = mb_substr($tmpMname,0,3);
        }
        $username = $tmpLastName."_".$tmpFname.$tmpMname;
        $username = str_replace(" ","",$username); //finally: remove all possible ocasional spaces

        //check if such username could exist
        do {
            $tmp_student_ldb = $this->Students->find()
                ->where(['user_name' => $name['uname']])
                ->first();
            if (!empty($tmp_student_ldb)) {
                $name['uname'] = $name['uname'].'1'; 
            }
        } while (!empty($tmp_student_ldb));

        return $username;
    }
    /*
     * Sync Students from ASU MKR into Local DataBase
     */
    private function _sync_ASU_with_LDB_users(){
        $this->loadModel('Students');
        $this->loadModel('Schools');
        //$this->_max_id();
        
        foreach($this->students_mkr as $student_of_asu_mkr){
            //Prepare and clean-up names on Ukrainian or English
            if ($student_of_asu_mkr['ST32']==804){ //ukrainians
                $asu_mkr_fname = $this->_name_cleanup($student_of_asu_mkr['ST3']);
                $asu_mkr_mname = $this->_name_cleanup($student_of_asu_mkr['ST4']);
                $asu_mkr_lname = $this->_name_cleanup($student_of_asu_mkr['ST2']);
            } else {                            //foreign
                $asu_mkr_fname = ($student_of_asu_mkr['ST75']!=null?$this->_name_cleanup($student_of_asu_mkr['ST75']):$this->_name_cleanup($student_of_asu_mkr['ST3']));
                $asu_mkr_mname = ($student_of_asu_mkr['ST76']!=null?$this->_name_cleanup($student_of_asu_mkr['ST76']):$this->_name_cleanup($student_of_asu_mkr['ST4']));
                $asu_mkr_lname = ($student_of_asu_mkr['ST74']!=null?$this->_name_cleanup($student_of_asu_mkr['ST74']):$this->_name_cleanup($student_of_asu_mkr['ST2']));
            }
            
            $name['fname'] = rtrim($asu_mkr_fname.' '.$asu_mkr_mname); //often happens with foreign persons - no middle name
            $name['lname'] = $asu_mkr_lname;
            // Generate new username
            
            $name['uname'] = $this->create_Google_username($name);
            
//var_dump("BEGIN_ACTIVE- name to search ASU MKR ID=".$student_of_asu_mkr['ST1']);
//var_dump($name);
//var_dump('std11='.$student_of_asu_mkr['STD11']);
//var_dump('f_id='.$student_of_asu_mkr['F1']);
//var_dump('pnsp_id='.$student_of_asu_mkr['PNSP1']);
//var_dump('sp_id='.$student_of_asu_mkr['SP1']);
            if (($student_of_asu_mkr['STD11']==0||$student_of_asu_mkr['STD11']==8)&&$student_of_asu_mkr['F1']>0&&$student_of_asu_mkr['PNSP1']>0&&$student_of_asu_mkr['SP1']>0){ //completely ACTIVE student!
                unset($student_ldb);
                $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 1); //get Active student's data
                if (isset($student_ldb)){
//var_dump("Found as ACTIVE - check if need to rename=".$student_ldb->last_name);
                    $upd_status = $this->_update_student_record_by_ASU($this->Students->get($student_ldb->id),$student_of_asu_mkr, $student_ldb, $name);
                    //if($rename>0){
                    if($upd_status[0]>0){
//var_dump("RENAME-active-strart=".$upd_status[1]);
                        //if ($this->Students->save($data)) {
                        if ($this->Students->save($upd_status[1])) {
                            $this->options['rename_student']++;
//var_dump("RENAME-active-ok! ".$this->options['rename_student']);                                
                            $this->status=true;
                        }
                    }
                } else {
                    unset($student_ldb);
                    $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 10); //get Archive student's data
                    if (isset($student_ldb)){
//var_dump("Found as ARCHIVE - check if need to rename=".$student_ldb->last_name);
                        $upd_status = $this->_update_student_record_by_ASU($this->Students->get($student_ldb->id),$student_of_asu_mkr, $student_ldb, $name);
                    
                        //if($rename>0){
                        if($upd_status[0]>0){
//var_dump("RENAME-get_form_archive-strart=".$upd_status[1]);
                            //if ($this->Students->save($data)) {
                            if ($this->Students->save($upd_status[1])) {
                                $this->options['rename_student']++;
//var_dump("RENAME-get_form_archive-ok! ".$this->options['rename_student']);                                
                                $this->status=true;
                            }
                        }
                    } else {
                        $createnew = true;
                        //Additional tests against archive types //TODO: create loop?
                        unset($student_ldb);
                        $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 3);
                        if (isset($student_ldb)){ $createnew = false; 
//var_dump("NEW-skip by status_clone! =".$student_ldb->id);
                        }
                        unset($student_ldb);
                        $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 7);
                        if (isset($student_ldb)){ $createnew = false; 
//var_dump("NEW-skip by status_ignore! =".$student_ldb->id);                        
                        }
                        unset($student_ldb);
                        $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 11);
                        if (isset($student_ldb)){ $createnew = false; 
//var_dump("NEW-skip by status_2faculty! =".$student_ldb->id);
                        }
                        unset($student_ldb);
                        $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 12);
                        if (isset($student_ldb)){ $createnew = false; 
//var_dump("NEW-skip by status_dontsync! =".$student_ldb->id);                        
                        }
                      if ($createnew) {
                        //add a new one student
                        $data = $this->Students->newEntity();
                        $data['student_id'] = $student_of_asu_mkr['ST1'];  //use new asu MKR ID
                        $data['asumkr_id'] = $student_of_asu_mkr['ST1'];
                        //$data['school_id'] = $student_of_asu_mkr['F1'];      //for gsync
                        $data['f_id'] = $student_of_asu_mkr['F1'];
                        //use old Contingent id for gsync
                        $school = $this->Schools->find()
                            ->where(['f_id' => $student_of_asu_mkr['F1']])
                            ->first();
                        if ($school) {
                            $data['school_id'] = $school->school_id;   //for gsync
                        }
                        $data['pnsp_id'] = $student_of_asu_mkr['PNSP1'];
                        $data['sp_id'] = $student_of_asu_mkr['SP1'];
                        $data['special_id'] = $student_of_asu_mkr['SP1']; //for gsync
                        $data['groupnum'] = $student_of_asu_mkr['GR3'];
                        $data['first_name'] = $name['fname'];
                        $data['last_name'] = $name['lname'];
                        $data['user_name'] = $name['uname'];
                        $data['grade_level'] = (!is_null($student_of_asu_mkr['ST71'])?$student_of_asu_mkr['ST71']:0);
                        $data['ipn_id']=$student_of_asu_mkr['ST15'];
                        $data['password'] = $this->_generate_pass();
                    
                        if (strlen($student_of_asu_mkr['ST108'])>1){    //store legacy CONTINGENT ID if exist
                            $data['c_stud_id'] = $student_of_asu_mkr['ST108'];  
                        }
                        //($student_of_asu_mkr['std11']<>2||$student_of_asu_mkr['std11']<>4) ?  $data['status_id'] = 1 :  $data['status_id'] = 10;//TODO:will newer occur?
                        $data['status_id'] = 1;
                        $student_login_clone = $this->Students->find()
                            ->where(['user_name' => $name['uname']])
                            ->first();

                        if (isset($student_login_clone)){   //check if clone
                            $data['status_id'] = 3;
                            $this->options['clone_login_in students']++;
                        }
//var_dump("NEW-start=".$data);
                        if ($this->Students->save($data)) {
                            $new_student_for_email++;
                            $this->options['new_student']++;
                            $this->status=true;
//var_dump("NEW-OK=".$data['asumkr_id']);
                        } else {
                            $this->options['new_student_failed']++;
//var_dump("NEW-failed=".$data['asumkr_id']);
                        }
                      } 
                    }
                }
            } else { //possible ARCHIVE student!
                if (($student_of_asu_mkr['STD11']>0&&$student_of_asu_mkr['STD11']<8)&&$student_of_asu_mkr['F1']>0&&$student_of_asu_mkr['PNSP1']>0&&$student_of_asu_mkr['SP1']>0){ //really ARCHIVE student!
                    unset($student_ldb);
                    $student_ldb = $this->_LDB_get_student_by_ASUID($student_of_asu_mkr, 1); //get Active student's data
                    if (isset($student_ldb)&&($student_ldb->id>0)){
                        $upd_status = $this->_update_student_record_by_ASU($this->Students->get($student_ldb->id),$student_of_asu_mkr, $student_ldb, $name);
                        $upd_status[1]['status_id'] = 10;  //force archive status
//var_dump("RENAME(2archive)-strart=".$upd_status[1]);
                        if ($this->Students->save($upd_status[1])) {
                            $this->options['rename_student']++;
                            $this->status=true;
//var_dump("RENAME(2archive)-ok! ".$this->options['rename_student']);
                        }
                    }
                }
            }
        }

        if(($this->options['rename_student']==0) and ($this->options['new_student']==0)){
            $this->message[]['message']="Sorry, there are no new records in ASU MKR database. Also, ".$this->options['new_student_failed']." students records failed to create!";
        }
        if (count($new_student_for_email)>0){
            //TODO: temporarily disabled!!!
            //$this->send_email($new_student_for_email,"There are ".$this->options['new_student']." new students in SysAdmin!. Also, ".$this->options['new_student_failed']." students records failed to create!");
        }
    }

    private function _sync_ASU_with_LDB_photo(){
        $this->loadModel('Students');
        foreach($this->students_mkr as $student_of_asu_mkr){
            $student_ldb = $this->Students->find()
                ->where(['asumkr_id ' => $student_of_asu_mkr['ST1']],['status_id'=>1])
                ->first();
            if (isset($student_ldb)){
                //get photo from BDD
                $this->students_mkr_photo = $this->asu_mkr->gets_bdd("SELECT foto3 FROM foto WHERE ((foto1=".$student_of_asu_mkr['ST1'].")AND(foto2=1));");
                //put photo ino file
                $img = ibase_blob_get(ibase_blob_open($students_mkr_photo[0]['PHOTO']), ibase_blob_info($students_mkr_photo[0]['PHOTO'])[0]);
//var_dump($students_mkr_photo[0]['PHOTO']);
//var_dump($img);
                //TODO: need debug first
                //file_put_contents('photo/'.$student_ldb['user_name'].'.jpg', $img);
                ibase_blob_close($students_mkr_photo[0]['PHOTO']);
            }
        }
        $this->message[]['message']='Sync photos ASU MKR -> LDB was successfully';

    }    
    
    private function _initial_update_ldb_affiliation_ids() {
        $conn = ConnectionManager::get('default');
        // Update faculties id's. Execute once - no more necessary
        $updatefaculty_sql = "UPDATE `students` SET `students`.`f_id` = (SELECT `schools`.`f_id` FROM `schools` WHERE  `schools`.`school_id`=`students`.`school_id`); ";
        $faculty_results = $conn->execute($updatefaculty_sql);
        //update specialities default as well as id's. Execute once - no more necessary
        $updatespeciality_sql = "
            ALTER TABLE `specials` ALTER `specials`.`pnsp_id` SET DEFAULT 0;
            ALTER TABLE `specials` ALTER `specials`.`sp_id` SET DEFAULT 0;
            ALTER TABLE `specials` ALTER `specials`.`cont_id` SET DEFAULT 0;
            ALTER TABLE `students` MODIFY `students`.`c_stud_id` VARCHAR(28);
            UPDATE `specials` SET `specials`.`pnsp_id`=0, `specials`.`sp_id`=0 WHERE ISNULL(`specials`.`pnsp_id`)=1;
            UPDATE `specials` SET `specials`.`cont_id`=0 WHERE ISNULL(`specials`.`cont_id`)=1;
            UPDATE `students` SET 
            `students`.`pnsp_id` = (SELECT `specials`.`pnsp_id` FROM `specials` WHERE  `specials`.`special_id`=`students`.`special_id`),
            `students`.`sp_id` = (SELECT `specials`.`sp_id` FROM `specials` WHERE  `specials`.`special_id`=`students`.`special_id`); 
            UPDATE `specials` SET `specials`.`special_id` = `specials`.`sp_id`;
            UPDATE `students` SET `students`.`c_stud_id` = `students`.`student_id`;               
            UPDATE `students` SET `students`.`special_id`=`students`.`sp_id`;
            CREATE INDEX idx_contid ON `students` (`c_stud_id`);
            CREATE INDEX idx_asumkrid ON `students` (`asumkr_id`);               
            ALTER TABLE `students` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci; 
            ";
        $speciality_results = $conn->execute($updatespeciality_sql);
        $this->message[]['message']='ASU MKR faculties and specialities IDs have been updated for students';
    }
    
    private function _initial_update_ldb_students_ids() {
        // Get CongingentID from LDB and insert into ASU MKR DB (if found student by full name)
        $this->loadModel('Students');
        $this->_max_id();

        $notfound = 0;
        $singleinstance = 0;
        $asuidingaps = 0;
        $contidinasu = 0;
        $ipnidinasu = 0;
        $multipleinstances = 0;
        $multipleresolved = 0;
        $LDBupdateOK = 0;
        $LDBupdateFail = 0;
        $txtreport = '';
        
        $notfound_pos = array();
        $found_multiple = array();
        
        foreach($this->students_mkr as $asu_arr_row=>$student_of_asu_mkr){
//var_dump("asu_ID=".$student_of_asu_mkr['ST1']);
//var_dump("asu_contID (if exist)=".$student_of_asu_mkr['ST108']);
//var_dump("asu_IPN (if exist)=".$student_of_asu_mkr['ST15']);
//var_dump("asu_last_name=".$student_of_asu_mkr['ST2']);
//var_dump("----------------------------------");
          if (!empty($student_of_asu_mkr['PNSP1'])&&!empty($student_of_asu_mkr['SP1'])) {
            
            $txtreport .= "START-asu_last_name=".$student_of_asu_mkr['ST2']."\r\n";
            $txtreport .= "asu_ID=".$student_of_asu_mkr['ST1']."\r\n";
            $txtreport .= "asu_contID (if exist)=".$student_of_asu_mkr['ST108']."\r\n";
            //ZERO check - against manually entered ASU MKR IDs:
            unset($students_ldb);
                $students_ldb = $this->Students->find()
                    ->where(['asumkr_id' => $student_of_asu_mkr['ST1']]);
            if(isset($students_ldb)&&($students_ldb->count()>0)){
                unset($student_ldb);
                foreach($students_ldb as $student_ldb){
                    $txtreport .= "RESULT -found-gaps-by-asumkrid1(isset)=".$student_ldb->asumkr_id.", status=".$student_ldb->status_id."\r\n";
                    $singleinstance++;
                    $asuidingaps++;
                }
            } else {
                //First check - is it kontingent ID existing
                if (!empty(trim($student_of_asu_mkr['ST108']))) {
                    unset($students_ldb);
                    $students_ldb = $this->Students->find()
                        ->where(['student_id' => trim($student_of_asu_mkr['ST108'])]);
                    if(isset($students_ldb)&&($students_ldb->count()>0)){
                        unset($student_ldb);
                        foreach($students_ldb as $student_ldb){
                            $txtreport .= "RESULT -found-gaps-by-contid1(isset)=".$student_ldb->student_id.", status=".$student_ldb->status_id." (count".$students_ldb->count().") \r\n";
                            $singleinstance++;
                            $contidinasu++;
                            //UPDATE LDB:
                            $data = $this->Students->get($student_ldb->id);
                            $data['asumkr_id']=$student_of_asu_mkr['ST1'];
                            //$data['student_id']=$student_of_asu_mkr['ST1'];//Disable - until GAPS break
                            if ($this->Students->save($data)) {
                                $LDBupdateOK++;
                                $txtreport .= "RESULT -found-gaps-by-contid1(isset), write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                            } else {
                                $LDBupdateFail++;
                                $txtreport .= "RESULT -found-gaps-by-contid1(isset), FAIL to write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                            }
                        }
                    }
                } else {
                    //Second check - is it IPN ID existing
                    //if (!empty($student_of_asu_mkr['ST15'])) {
                    if (mb_strlen(trim($student_of_asu_mkr['ST15']))>5) {
                        //if (mb_strlen($student_of_asu_mkr['ST15'])>3)
                        unset($students_ldb);
                        $students_ldb = $this->Students->find()
                            ->where(['ipn_id' => trim($student_of_asu_mkr['ST15'])]);
                        if(isset($students_ldb)&&($students_ldb->count()>0)){
                            unset($student_ldb);
                            foreach($students_ldb as $student_ldb){
                                $txtreport .= "RESULT -found-gaps-by-ionid1(isset)=".$student_ldb->ipn_id.", status=".$student_ldb->status_id."\r\n";
                                $singleinstance++;
                                $ipnidinasu++;
                                //UPDATE LDB:
                                $data = $this->Students->get($student_ldb->id);
                                $data['asumkr_id']=$student_of_asu_mkr['ST1'];
                                //$data['student_id']=$student_of_asu_mkr['ST1'];//Disable - until GAPS break
                                if ($this->Students->save($data)) {
                                    $LDBupdateOK++;
                                    $txtreport .= "RESULT -found-gaps-by-ipnid1(isset), write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                } else {
                                    $LDBupdateFail++;
                                    $txtreport .= "RESULT -found-gaps-by-ipnid1(isset), FAIL to write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                }
                            }
                        }
                    } else {
                        // clean-up names - LDB has cleaned values!
                        if ($student_of_asu_mkr['ST32']==804){ //ukrainians
                            $asu_mkr_fname = $this->_name_cleanup($student_of_asu_mkr['ST3']);
                            $asu_mkr_mname = $this->_name_cleanup($student_of_asu_mkr['ST4']);
                            $asu_mkr_lname = $this->_name_cleanup($student_of_asu_mkr['ST2']);
                        } else {                            //foreign
                            $asu_mkr_fname = $this->_name_cleanup($student_of_asu_mkr['ST75']);
                            $asu_mkr_mname = $this->_name_cleanup($student_of_asu_mkr['ST76']);
                            $asu_mkr_lname = $this->_name_cleanup($student_of_asu_mkr['ST74']);            
                        }
            
                        $asu_mkr_search_fname = rtrim($asu_mkr_fname.' '.$asu_mkr_mname); //often happens with foreign persons - no middle name
                        $txtreport .= "STEP2-search by ASU names=".$asu_mkr_lname." ".$asu_mkr_search_fname."\r\n";
                        $found_pos=array();
                        $found_pos2=array();

                        // Recommended update LDB first - to remove duplication of spaces and trailing spaces....
                        unset($students_ldb);
                        $students_ldb = $this->Students->find('all')
                            ->where(['first_name' => $asu_mkr_search_fname])
                            ->where(['last_name' => $asu_mkr_lname]);
                        if (isset($students_ldb)&&($students_ldb->count()>0)){
                            unset($student_ldb);
                            foreach($students_ldb as $student_ldb){
                                $found_pos[$student_ldb->id] = $student_ldb->student_id;
                                $found_pos2[$student_ldb->id] = array('LDB_ID'=>$student_ldb->id, 'contID'=>$student_ldb->student_id, 'FName'=>$student_ldb->first_name, 'LName'=>$student_ldb->last_name, 'statusID'=>$student_ldb->status_id);
                            }
                            if (count($found_pos)==0) { // NOT FOUND!
                                $notfound++;
                                $notfound_pos[] = $student_of_asu_mkr;
                                $txtreport .= "RESULT - NOT FOUND!\r\n";
                            } elseif(count($found_pos)==1) { // found - SINGLE OCCURENCE - OK!
                                $singleinstance++;
                                $found_keys = array_keys($found_pos);
                                $asu_mkr_update_sql = "UPDATE ST SET ST.ST108=".$found_pos[$found_keys[0]]." WHERE ST.ST1=".$student_of_asu_mkr['ST1'].";";
                                $txtreport .= "RESULT -found-by-name(single): ContID=".$found_pos[$found_keys[0]]."(".$found_pos2[$found_keys[0]]['contID']."), Name=".$found_pos2[$found_keys[0]]['LName']." ".$found_pos2[$found_keys[0]]['FName']."\r\n";
                                //UPDATE ASU MKR DATABASE:
                                $results = $this->asu_mkr->sets($asu_mkr_update_sql);
                                //UPDATE LDB:
                                $data = $this->Students->get($student_ldb->id);
                                $data['asumkr_id']=$student_of_asu_mkr['ST1'];
                                //$data['student_id']=$student_of_asu_mkr['ST1'];//Disable - until GAPS break
                                if ($this->Students->save($data)) {
                                    $LDBupdateOK++;
                                    $txtreport .= "RESULT -found-by-name(single), write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                } else {
                                    $LDBupdateFail++;
                                    $txtreport .= "RESULT -found-by-name(single), FAIL to write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                }
                            } else { // found - MULTIPLE OCCYURENCES
                                $multipleinstances++;
                                $found_multiple = array_merge($found_multiple,$found_pos2);
                                //update ASU MRR DB for only active student's
                                foreach ($found_pos2 as $student2resolve) {
                                    if ($student2resolve['statusID'] == 1){
                                        $multipleresolved++;
                                        $txtreport .= "RESULT -found-by-name(from multiple): ContID=".$student2resolve['contID'].", Name=".$student2resolve['LName']." ".$student2resolve['FName']."\r\n";
                                        //UPDATE ASU MKR DATABASE:
                                        $asu_mkr_update_sql = "UPDATE ST SET ST.ST108=".$student2resolve['contID']." WHERE ST.ST1=".$student_of_asu_mkr['ST1'].";";
                                        $results = $this->asu_mkr->sets($asu_mkr_update_sql);
                                        //UPDATE LDB:
                                        $data = $this->Students->get($student_ldb->id);
                                        $data['asumkr_id']=$student_of_asu_mkr['ST1'];
                                        //$data['student_id']=$student_of_asu_mkr['ST1'];//Disable - until GAPS break
                                        if ($this->Students->save($data)) {
                                            $LDBupdateOK++;
                                            $txtreport .= "RESULT -found-by-name(from multiple), write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                        } else {
                                            $LDBupdateFail++;
                                            $txtreport .= "RESULT -found-by-name(from multiple), FAIL to write ASU MKR ID=".$student_of_asu_mkr['ST1']." INSTEAD of student_id \r\n";
                                        }
                                    }
                                }
                            } 
                        } else {
                            $notfound++;
                            $notfound_pos[] = $student_of_asu_mkr;
                            $txtreport .= "RESULT - NOT FOUND(isset)!\r\n";
                        }
                    }
                }
            }
          }
        }

        //prepare total repor message
        $totalresultmessage = 'Not found='.$notfound.' Found single='.$singleinstance.' (ContIDinASU='.$contidinasu.', IPNIDinASU='.$ipnidinasu.', ASUIDinGAPS= '.$asuidingaps.') Found MULTIPLE='.$multipleinstances.' Resolved MULTIPLE='.$multipleresolved.' UPDATE LDB OK='.$LDBupdateOK.' UPDATE LDB Fail='.$LDBupdateFail;
        $this->message[]['message']= $totalresultmessage;
        $txtreport .= "\r\n".$totalresultmessage;
        //save all report files
        file_put_contents (ROOT.DS."webroot".DS."files".DS."report.txt", $txtreport);
        $Csv = new CsvComponent($this->options_csv);
        $Csv->export_simple(ROOT.DS."webroot".DS."files".DS."notfound.csv", $notfound_pos);
        $Csv->export_simple(ROOT.DS."webroot".DS."files".DS."duplicate.csv", $found_multiple);
    }
    
    private function _initial_update_asumkr_portal_userdata (){
        $this->loadModel('Students');
        //$this->_get_asu_mkr_portal_users();
        $newportaluser = 0;
        $dbwriteerrors = 0;
        $missed = 0;
        $students_ldb = $this->Students->find('all');
        foreach($students_ldb as $student_ldb){
            //process only active students!
            if ($student_ldb->status_id ==1) {
                //check if user has been already registered on portal - by username
                //$this->_get_asu_mkr_portal_user($student_ldb->user_name, 0);
                //check if user has been already registered on portal - by asumkr_id
                $this->_get_asu_mkr_portal_user_by_id($student_ldb->asumkr_id, 0);
                //check if user has been already registered on portal - by tdmu's email! 
                $this->_get_asu_mkr_portal_user_by_email($student_ldb->user_name.'@tdmu.edu.ua', 0);
    //var_dump($this->_get_asu_mkr_portal_user($student_ldb->user_name, 0));
                if (is_null($this->asu_mkr_portal_users)&&is_null($this->asu_mkr_portal_users_email)&&!is_null($student_ldb->asumkr_id)){
    //var_dump($student_ldb);
                    $new_id = $this->asu_mkr->get_newID('GEN_USERS', 1);
                    if ($new_id){
    //var_dump($new_id);                
                        $salt = $this->_asu_portal_generateSalt();
                        $pass = $this->_asu_portal_setPassword($student_ldb->password, $salt);
                        $u12key = $this->_asu_portal_generateU12();
                        // SQL to create a new portal user 
                        $asu_mkr_insert_sql = "INSERT INTO users (u1,u2,u3,u4,u5,u6,u7,u8,u9,u10,u12) VALUES(
                                                ".$new_id.",
                                                '".$student_ldb->user_name."',
                                                '".$pass."',
                                                '".$student_ldb->user_name."@tdmu.edu.ua',
                                                0,
                                                ".$student_ldb->asumkr_id.",
                                                0,
                                                0,
                                                '".$salt."',
                                                0,
                                                '".$u12key."');";
    //print_r($asu_mkr_insert_sql);
                        $results = $this->asu_mkr->sets($asu_mkr_insert_sql);  //disable during debug
                        if ($results){
                            $newportaluser++;
                        } else {
                            $dbwriteerrors++;
                        }
                    }
                } else { //TODO: debug only
                    //var_dump($student_ldb->student_id);
                    $missed++;
                }
            }
        }
        $this->message[]['message']= $newportaluser.' new portal users has been created! '.$dbwriteerrors.' DB write errors. '.$missed.' records missed';
    }
    
    private function _fix_asumkr_portal_useremails (){
        $this->loadModel('Students');
        $newportaluser = 0;
        $dbwriteerrors = 0;
        $missed = 0;
        $students_ldb = $this->Students->find('all');
        foreach($students_ldb as $student_ldb){
            //check if user has been already registered on portal - by asumkr_id
            if (!is_null($student_ldb->asumkr_id)){
            $this->_get_asu_mkr_portal_user_by_id($student_ldb->asumkr_id, 0);
            if (!is_null($this->asu_mkr_portal_users)){
                if (!strpos($this->asu_mkr_portal_users[1]['U4'], '@tdmu.edu.ua')){
//var_dump($this->asu_mkr_portal_users[1]['U4']);
//var_dump(strpos($this->asu_mkr_portal_users[1]['U4'], '@tdmu.edu.ua' ));
                    // SQL to update a portal user 
                    $asu_mkr_insert_sql = "UPDATE users SET users.u4='".$student_ldb->user_name."@tdmu.edu.ua' WHERE users.u6=".$student_ldb->asumkr_id.";";
//print_r($asu_mkr_insert_sql);                    
                    $results = $this->asu_mkr->sets($asu_mkr_insert_sql);  //disable during debug
//var_dump($results);
                    if ($results){
                        $newportaluser++;
                    } else {
                        $dbwriteerrors++;
                    }
                } else {
                    $missed++;
                }
            } else { //TODO: debug only
                $missed++;
            }
            }
        }
        $this->message[]['message']= $newportaluser.' portal users has been fixed! '.$dbwriteerrors.' DB write errors. '.$missed.' records skipped';
    }

    private function _initial_update_asumkr_portal_teacherdata ($uploadFile){
        $newportaluser = 0;
        $dbwriteerrors = 0;
        $multipleinstances = 0;
        $missed = 0;
        
        $Csv = new CsvComponent($this->options_csv);
        $teacherarr = $Csv->import_simple($uploadFile,null,array("delimiter" => ";"));
//var_dump($teacherarr);
//var_dump($teacherarr[0]['IPN']);
//die();            
        foreach($teacherarr as $row=>$teacherData) {
            if ($row >0) {
                $this->_get_teachers_asu_mkr($teacherData['IPN']); // search teacher by IPN
                if (!is_null($this->teachers_mkr)){            //create portal users only for teachers who are existing in ASU MKR 
                    if (count($this->teachers_mkr) > 1){
                        $multipleinstances++;
                    } else {
                        $tmpusername = explode("@", $teacherData['EMAIL']);
                        $teacherDataUsername = $tmpusername[0];
//var_dump($teacherDataUsername);                        
//var_dump($this->teachers_mkr[1]);
                        $this->_get_asu_mkr_portal_user($teacherDataUsername, 1);
                        if (is_null($this->asu_mkr_portal_users)){
//var_dump($student_ldb);
                            $new_id = $this->asu_mkr->get_newID('GEN_USERS', 1);
                            if ($new_id){
//var_dump($new_id);                
                                $salt = $this->_asu_portal_generateSalt();
                                $pass = $this->_asu_portal_setPassword($this->_generate_pass(), $salt);
                                $u12key = $this->_asu_portal_generateU12();
                                // SQL to create a new portal user 
                                $asu_mkr_insert_sql = "INSERT INTO users (u1,u2,u3,u4,u5,u6,u7,u8,u9,u10,u12) VALUES(
                                            ".$new_id.",
                                            '".$teacherDataUsername."',
                                            '".$pass."',
                                            '".$teacherDataUsername."@tdmu.edu.ua',
                                            1,
                                            '".$this->teachers_mkr[1]['P1']."',
                                            0,
                                            0,
                                            '".$salt."',
                                            0,
                                            '".$u12key."');";
//var_dump($asu_mkr_insert_sql);
                                $results = $this->asu_mkr->sets($asu_mkr_insert_sql);  //disable during debug
                                if ($results){
                                    $newportaluser++;
                                } else {
                                    $dbwriteerrors++;
                                }
                            }
                        } else { //TODO: debug only
                //var_dump($student_ldb->student_id);
                            $missed++;
                        }                        
                    } 
                }
            }
        }
        $this->message[]['message']= $newportaluser.' new portal users (for Teachers!) has been created! '.$dbwriteerrors.' DB write errors. '.$missed.' records missed. '.$multipleinstances.' unreasonable duplicates in ASU MKR teachers table';
    }
    
    /*
     * clean-up string (especially - for names clean-up)
     */
    private function _name_cleanup($str){ 
        $str = trim($str);
        $str = str_replace("(","",$str);
        $str = str_replace(")","",$str);
        $str = str_replace("-","",$str);
        $str = str_replace("'","",$str);
        $str = str_replace(":","",$str);
        $str = str_replace(".","",$str);
        $str = str_replace("`","",$str);
        $str = str_replace("\"","",$str);
        
        return $str;
    }
    
    /*
     * clean-up LDB name`s strings
     */
    private function _LDB_names_cleanup(){
        $this->loadModel('Students');
        $this->_max_id();
        $Students_ldb = $this->Students->find('all');
        foreach ($Students_ldb as $Student_ldb) {
            $Student_ldb->last_name = trim($Student_ldb->last_name);
            $Student_ldb->first_name = trim($Student_ldb->first_name);
            $Student_ldb->first_name = str_replace("  ", " ", $Student_ldb->first_name);
            $this->Students->save($Student_ldb);
        }
        $this->message[]['message'] = "The Student`s names has been updated and saved";
    }
    
    private function _initial_LDB_dbstructure_upgrade(){
        $conn = ConnectionManager::get('default');
        $updatefaculty_structure_sql = "ALTER TABLE `schools` COLLATE utf8_general_ci;
                                        ALTER TABLE `schools` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
                                        ALTER TABLE `schools` ADD cont_id int(3) AFTER name;
                                        ALTER TABLE `schools` ADD f_id int(3) AFTER cont_id;
                                        UPDATE `schools` SET cont_id = school_id; ";
        //$conn->begin();
        //$faculty_results = $conn->query($updatefaculty_structure_sql);
        //$conn->commit();

        $updatespecials_structure_sql = "ALTER TABLE `specials` COLLATE utf8_general_ci; 
                                         ALTER TABLE `specials` ADD cont_id int AFTER code; 
                                         ALTER TABLE `specials` ADD pnsp_id int AFTER cont_id; 
                                         ALTER TABLE `specials` ADD sp_id int AFTER pnsp_id; 
                                         UPDATE `specials` SET cont_id = special_id; ";
        
        $updatestyudent_structure_sql = "ALTER TABLE `students` ADD c_stud_id text AFTER send_photo_google; 
                                         ALTER TABLE `students` ADD c_school_id int(3) AFTER c_stud_id; 
                                         ALTER TABLE `students` ADD c_sprec_id int(5) AFTER c_school_id; 
                                         ALTER TABLE `students` ADD f_id int(3) AFTER c_sprec_id; 
                                         ALTER TABLE `students` ADD pnsp_id int AFTER f_id; 
                                         ALTER TABLE `students` ADD sp_id int AFTER pnsp_id; 
                                         ALTER TABLE `students` ADD asumkr_id int AFTER sp_id;
                                         UPDATE `students` SET c_stud_id = student_id; 
                                         UPDATE `students` SET c_sprec_id = special_id; 
                                         UPDATE `students` SET c_school_id = school_id; ";
                                         
        $update_structure_sql =  $updatefaculty_structure_sql.$updatespecials_structure_sql.$updatestyudent_structure_sql;
        //apply DB structure update
        $conn->begin();
        $results = $conn->execute($update_structure_sql);
        $conn->commit();
        if ($results){
            $this->message[]['message']='LDB tables structure updated SUCCESSFULY';
        } else {
            $this->message[]['message']='FAILED to update LDB tables structure!';
        }
    }
    
    private function _asu_portal_setPassword($password,$salt){
		$encrypted_password = crypt($password,$salt);
        return $encrypted_password;
		//$this->sendChangePasswordMail($password);
	}
    
    private function _asu_portal_generateSalt(){
		$salt = openssl_random_pseudo_bytes(12);
		$hex   = bin2hex($salt);
		$salt = '$1$' .$hex /*strtr($salt, array('_' => '.', '~' => '/'))*/;

		return $salt;  //$this->u9 in asu
	}

	protected function _asu_portal_generateU12(){
		$token = openssl_random_pseudo_bytes(10);
		$key   = bin2hex($token);
        return $key;
		//$this->saveAttributes(array('u12'=>$key));
	}
    
}
