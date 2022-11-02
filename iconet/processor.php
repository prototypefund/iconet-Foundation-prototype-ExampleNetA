<?php
namespace iconet;
class processor
{
    //logged in user
    protected mixed $user;

    //helper classes
    protected database $db; //database
    protected post_office $po; //postoffice
    protected package_builder $pb; //package_builder
    protected cryptograph $cryp; //cryptograph
    protected package_handler $ph;
    protected string $path_postings ;

    public function __construct($userLoggedIn)
    {
        $this->db = new database();
        $this->po = new post_office();
        $this->cryp = new cryptograph();
        $this->pb = new package_builder();
        $this->ph = new package_handler();
        $this->path_postings = ".\iconet\posts\id.";

        $user = $this->db->get_user_by_name($userLoggedIn);
        $this->setUser($user);
    }


    /**
     * @param mixed $user
     */public function setUser(mixed $user): void
{
    $this->user = $user;
}
    function get_external_pubkey($address){
        $msg = $this->pb->request_pubkey($address);
        $response = $this->po->send("url", $msg);
    }

    function create_iconet_post($content){
        //generate random ID
        $done = false;
        $ID ="";
        while(!$done){
            $ID = md5(rand()); // generate random ID
            if(!$this->db->get_post_by_ID($ID)) $done = true; //Repeat if ID already in use (unlikely but possible)
        }
        //generate notification
        $notification = $content . "- notification text"; //for testing content is only string

        $predata['id'] = $ID;
        $predata['notification'] = $notification;

        //encrypt notification & content
        $secret = $this->cryp->genSymKey();
        $enc_not = $this->cryp->encSym(Json_encode($predata),$secret);
        $enc_cont = $this->cryp->encSym($content,$secret);

        //save content
        $file = fopen($this->path_postings.$ID.".txt", "w") or die("Cannot open file.");
        // Write data to the file
        fwrite($file, $enc_cont);
        // Close the file
        fclose($file);

        //save post in db
        $this->db->add_post($ID, $this->user['username'], $secret);

        //generate and send notifications

        $contacts = $this->db->get_contacts($this->user['username']);
        if ($contacts == null) {
            echo "<br>You need contacts generate something for them! <br>";
            return null;
        }

        $ciphers= $this->cryp->genAllCiphers($contacts, $secret);
        foreach ($ciphers as $c){
            $notif_package = $this->pb->send_notification($this->user['address'], $c, $enc_not);
            $response = $this->po->send("url", $notif_package);
        }

    }

    function check_inbox($username){
         return $this->db->get_notifications($username);
    }

    function display_content($id, $from, $secret){
         $msg = $this->pb->request_content($id, $from);
         $response = $this->po->send("url", $msg);
         $package = Json_decode($response,true);
        if (0!=strcmp($this->ph->check_package($package),"Send Content")){
            echo "Error - invalid response Package. Expected: Send Content";
            return "Error - invalid response Package. Expected: Send Content";
        }
        $content = Json_decode($package['content'],true);
        $main_content = $this->cryp->decSym($content['content'], $secret);
        if (isset($content['interactions'])){
            foreach ($content['interactions'] as $i){
                $inter = $this->cryp->decSym($i['enc_int'], $secret);
                $main_content .= "<br>Comment from: ". $i['sender'] . "<br>".$inter;
            }
        }

        return $main_content;

    }

    //decrypts and saves incoming notifications, returns potential errors
    public function save_notification(mixed $package)
    {
        $error = false;
        $username = $this->user['username'];
        $link = "";
        $sender = $package['sender'];

        $cipher = $package['cipher'];
        $enc_pre = $package['predata'];
        $privkey = $this->db->get_privkey_by_address($package['to']); // todo check if user is logged in / privkey may be accessed
        $secret = $this->cryp->decAsym($package['cipher'], $privkey);

        $predata = Json_decode( $this->cryp->decSym($enc_pre, $secret) , true);

        $id = $predata['id'];
        $text = $predata['notification'];

        $this->db->add_notification($id, $username, $sender, $secret, $link, $text);
        if (!$error) return false; else return $error;
    }

    function get_format($format){
         $msg = $this->pb->request_format($format);
         $response = $this->po->send("url", $msg);
         $package = json_decode($response, true);
         if (0==strcmp($this->ph->check_package($package),"Send Format"))
             return $package['format'];
         else return false;
    }

    function read_content($id)
    {
        $post = $this->db->get_post_by_id($id);
        if(!$post){
            echo "<br>Error - Unknown ID <br>";
            return "Error - Unknown ID";
        }else {
            if (!$post['username'] == $this->user['username']){
                echo "<br>Error - Wrong User <br>";
                return "Error - Wrong User";
            } else {
                $filename = $this->path_postings. $id. ".txt";
                $myfile = fopen($filename, "r") or die("Error - Unable to open file!");
                $content['content'] =  fread($myfile,filesize($filename));
                fclose($myfile);

                $interactions_db = $this->db->get_interactions_by_contentid($id);
                $inters= array();
                $i=0;
                foreach ($interactions_db as $in){
                    $inter['sender'] = $in['sender'];
                    $inter['enc_int'] = $in['enc_int'];
                    $inters[$i] = $inter;
                }
                $content['interactions'] = $inters;
                return json_encode($content);
            }

        }

    }

    function post_interaction($interaction, $content_id, $from, $to, $type, $secret){
         $enc_int = $this->cryp->encSym($interaction, $secret);

         $msg = $this->pb->send_interaction($from, $to, $content_id, $type, $enc_int);
         $response = $this->po->send("url", "$msg");
         return $response;

     }

    function process_interaction(mixed $package)
    {
        $error = false;
        if (!($this->user['address'] ==  $package['to'])){
            return "Error - Not owner of interacted content";
        }
        $username = $this->user['username'];
        $this->db->add_interaction($package['id'], $username, $package['sender'], $package['int_type'], $package['interaction']);
        if (!$error) return false; else return $error;
    }


}