<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Addons extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $addons_m;
    public $form_validation;
    public $data;
    public $signin_m;
    public $uri;
    /*
    | -----------------------------------------------------
    | PRODUCT NAME:     INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:            INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:            info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:        RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:            http://inilabs.net
    | -----------------------------------------------------
     */
    protected $downloadPath         = FCPATH . 'uploads/addons';
    protected $uploadPath           = APPPATH . 'uploads/addons/addons';
    protected $jsonName             = 'addons';
    protected $downloadFileWithPath = '';
    protected $downloadExtractPath  = '';
    protected $addons               = [];
    protected $addonID              = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->library("session");
        $this->load->model("site_m");
        $this->load->model("addons_m");

        $language = $this->session->userdata('lang');
        $this->lang->load('addons', $language);

        if ( config_item('demo') ) {
            $this->session->set_flashdata('error', 'In demo addon module is disable!');
             redirect(base_url('dashboard/index'));
        }
    }

    public function rules()
    {
        return [
            [
                'field' => 'file',
                'label' => $this->lang->line("addons_file"),
                'rules' => 'trim|max_length[200]|xss_clean|callback_fileUpload',
            ],
        ];
    }

    public function fileUpload()
    {
        if ($_FILES["file"]['name'] != "") {
            $file_name = $_FILES["file"]['name'];
            $explode   = explode('.', (string) $file_name);
            if (customCompute($explode) >= 2) {
                if (end($explode) == 'zip') {
                    $browseFileUpload = $this->browseFileUpload($_FILES);
                    if ($browseFileUpload->status) {
                        if (file_exists($this->downloadFileWithPath)) {
                            $fileUnZip = $this->fileUnZip();
                            if ($fileUnZip->status) {
                                if (file_exists("{$this->downloadPath}/{$browseFileUpload->file}/{$this->jsonName}.json")) {
                                    $json = file_get_contents("{$this->downloadPath}/{$browseFileUpload->file}/{$this->jsonName}.json");
                                    if ($json !== '' && $json !== false) {
                                        $obj    = json_decode($json);
                                        $path   = "{$this->downloadPath}/{$obj->init}";
                                        $addons = pluck($this->addons_m->get_addons(), 'obj', 'package_name');

                                        if (isset($addons[$obj->name]) && $addons[$obj->name]->version == $obj->version) {
                                            $this->form_validation->set_message("fileUpload", "This addon already installed.");
                                            return false;
                                        } elseif (file_exists($path) && is_readable($path) && include ($path)) {
                                            $path         = "{$this->downloadPath}/{$browseFileUpload->file}/src/file/";
                                            $destination  = rtrim((string) FCPATH, '/');
                                            $init         = new Init();
                                            $adsonInstall = $init->up($path, $destination);
                                            if ($adsonInstall) {
                                                $array = [
                                                    'package_name'  => $obj->name,
                                                    'description'   => $obj->description,
                                                    'version'       => $obj->version,
                                                    'slug'          => $obj->slug,
                                                    'author'        => $obj->author,
                                                    'init'          => $obj->init,
                                                    'files'         => json_encode($obj->files),
                                                    'preview_image' => $obj->preview_image,
                                                    'date'          => date('Y-m-d H:i:s'),
                                                    'userID'        => $this->session->userdata('loginuserID'),
                                                    'usertypeID'    => $this->session->userdata('usertypeID'),
                                                    'status'        => 1,
                                                ];

                                                if(isset($addons[$obj->name])) {
                                                    $this->addonID = $addons[$obj->name]->addonsID;
                                                }

                                                $this->addons = $array;
                                                return true;
                                            } else {
                                                $this->form_validation->set_message("fileUpload", "File distribution failed.");
                                                return false;
                                            }
                                        } else {
                                            $this->form_validation->set_message("fileUpload", "init file does not exist.");
                                            return false;
                                        }
                                    } else {
                                        $this->form_validation->set_message("fileUpload", "JSON content is empty.");
                                        return false;
                                    }
                                } else {
                                    $this->form_validation->set_message("fileUpload", "JSON file does not exist.");
                                    return false;
                                }
                            } else {
                                $this->form_validation->set_message("fileUpload", "Zip extract fail.");
                                return false;
                            }
                        } else {
                            $this->form_validation->set_message("fileUpload", "Upload file path does not exist.");
                            return false;
                        }
                    } else {
                        $this->form_validation->set_message("fileUpload", "File does not update. set permission in upload folder in 777.");
                        return false;
                    }
                } else {
                    $this->form_validation->set_message("fileUpload", "Upload a zip file.");
                    return false;
                }
            } else {
                $this->form_validation->set_message("fileUpload", "Invalid file.");
                return false;
            }
        } else {
            $this->form_validation->set_message("fileUpload", "Upload a file.");
            return false;
        }
    }

    public function index()
    {
        $this->data['addons'] = $this->addons_m->get_order_by_addons();

        if (isset($_FILES['file'])) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "addons/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                if($this->addonID) {
                    $this->addons_m->update_addons($this->addons, $this->addonID);
                } else {
                    $this->addons_m->insert_addons($this->addons);
                }
                $this->signin_m->signout();
                redirect(base_url("signin/index"));
            }
        } else {
            $this->data["subview"] = "addons/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function rollback()
    {
        if (permissionChecker('addons')) {
            $id = htmlentities((string) escapeString($this->uri->segment(3)));
            if ((int) $id !== 0) {
                $this->data['addon'] = $this->addons_m->get_single_addons(array('addonsID' => $id));
                if (is_object($this->data['addon'])) {
                    if ($this->data['addon']->files) {
                        $path = FCPATH . 'uploads/addons/' . $this->data['addon']->init;
                        if (file_exists($path) && is_readable($path) && include ($path)) {
                            $init        = new Init();
                            $addonDelete = $init->down();
                            if ($addonDelete) {
                                $this->deleteZipAndFile(FCPATH . 'uploads/addons/' . $this->data['addon']->slug . '.zip');
                                $this->addons_m->delete_addons($id);
                                $this->signin_m->signout();
                                redirect(base_url("signin/index"));
                            } else {
                                $this->session->set_flashdata('error', 'something wrong');
                                redirect(base_url("addons/index"));
                            }
                        } else {
                            $this->session->set_flashdata('error', 'addon already delete');
                            redirect(base_url("addons/index"));
                        }
                    } else {
                        $this->session->set_flashdata('error', 'Addon file not found');
                        redirect(base_url("addons/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', 'Addons not found');
                    redirect(base_url("addons/index"));
                }
            } else {
                $this->session->set_flashdata('error', 'Addon id not found');
                redirect(base_url("addons/index"));
            }
        } else {
            $this->session->set_flashdata('error', 'You do not have delete permission');
            redirect(base_url("addons/index"));
        }
    }

    private function browseFileUpload($file)
    {
        $returnArray['status']  = false;
        $returnArray['file']    = 'none';
        $returnArray['message'] = 'File not found';

        if (isset($file['file'])) {
            $fileName = $file['file']['name'];
            $fileSize = $file['file']['size'];
            $fileTmp  = $file['file']['tmp_name'];
            $fileType = $file['file']['type'];
            $endArray = explode('.', (string) $file['file']['name']);
            $fileExt  = strtolower(end($endArray));

            $extensions  = ["zip"];
            $maxFileSize = 1073741824;

            if (in_array($fileExt, $extensions)) {
                if ($fileSize <= $maxFileSize) {
                    move_uploaded_file($fileTmp, $this->downloadPath . '/' . $fileName);
                    $this->downloadFileWithPath = $this->downloadPath . '/' . $fileName;
                    $returnArray['status']      = true;
                    $returnArray['file']        = str_replace('.zip', '', (string) $fileName);
                    $returnArray['message']     = 'Success';
                } else {
                    $returnArray['message'] = "You max file size is 1 GB";
                }
            } else {
                $returnArray['message'] = "Please choose a zip file";
            }
        }

        return (object) $returnArray;
    }

    private function fileUnZip()
    {
        $returnArray['status']  = false;
        $returnArray['message'] = 'Error';
        $zip                    = new ZipArchive;
        if ($zip->open($this->downloadFileWithPath) === true) {
            $zip->extractTo($this->downloadPath);
            $zip->close();
            $returnArray['status']  = true;
            $returnArray['message'] = 'Success';
        } else {
            $returnArray['message'] = 'The update zip does not found';
        }

        return (object) $returnArray;
    }

    private function deleteZipAndFile($filePathAndName)
    {
        $returnArray['status']  = false;
        $returnArray['message'] = 'Error';

        try {
            if (file_exists($filePathAndName)) {
                unlink($filePathAndName);
                $filePathAndName = str_replace(".zip", "", (string) $filePathAndName);
                $this->rmdirRecursive($filePathAndName);
            }

            $returnArray['status']  = true;
            $returnArray['message'] = 'Success';
        } catch (Exception $e) {
            $returnArray['message'] = 'File delete permission problem';
        }

        return (object) $returnArray;
    }

    private function rmdirRecursive($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->rmdirRecursive($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
