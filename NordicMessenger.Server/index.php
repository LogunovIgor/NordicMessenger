<?php
    $manager = new MessageManager();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        echo json_encode($manager->GetAllMessages());

        http_response_code(200);
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $inputMessage = trim($_POST['message']);

        if (!empty($inputMessage)) {
            $message = new Message(uniqid(), $inputMessage, date('Y-m-d H:i:s'));
            $manager->AddMessage($message);

            http_response_code(200);
            return;
        }
    }

    http_response_code(400);
    die("Bad Request");
?>

<?php
    class Message {
        public $id;
        public $content;
        public $dateTime;

        public function __construct($id, $content, $dateTime) {
            $this->id = $id;
            $this->content = $content;
            $this->dateTime = $dateTime;
        }
    }

    class MessageManager {
        private $storage;

        public function __construct() {
            $this->storage = new FileStorage('messages.txt');
        }

        public function AddMessage($message) {
            $messages = $this->GetAllMessages();
            $messages[] = $message;
            $this->SaveMessages($messages);
        }

        public function UpdateMessage($message) {
            $messages = $this->GetAllMessages();
            $messageIndex = $this->GetIndexById($messages, $message->id);

            if ($messageIndex == -1)
                return;

            $messages[$messageIndex] = $message;
            $this->SaveMessages($messages);
        }

        public function GetAllMessages() {
            $content = json_decode($this->storage->GetFileContent());
            $messages = MessageConverter::ConvertAssociativeArrayToMessages($content);

            return $messages;
        }

        public function GetMessageById($id) {
            $messages = $this->GetAllMessages();
            $messageIndex = $this->GetIndexById($messages, $id);

            if ($messageIndex >= 0)
                return $messages[$messageIndex];
        }

        public function RemoveMessage($id) {
            $messages = $this->GetAllMessages();
            $messageIndex = $this->GetIndexById($messages, $id);

            if ($messageIndex == -1)
                return;

            array_splice($messages, $messageIndex, 1);
            $this->SaveMessages($messages);
        }

        private function SaveMessages($messages) {
            $content = json_encode($messages);
            $this->storage->SetFileContent($content);
        }

        private function GetIndexById($messages, $id) {
            if ($id != null) {
                for($i = 0; $i < count($messages); $i++) {
                    if ($messages[$i]->id == $id)
                        return $i;
                }
            }

            return -1;
        }
    }

    class FileStorage {
        private $fileName;

        public function __construct($fileName) {
            $this->fileName = $fileName;
        }

        public function GetFileContent() {
            if (!file_exists($this->fileName) || filesize($this->fileName) == 0)
                $this->SetFileContent('[]');

            return file_get_contents($this->fileName);
        }

        public function SetFileContent($content) {
            file_put_contents($this->fileName, $content);
        }
    }

    class MessageConverter {
        public static function ConvertAssociativeArrayToMessages($array) {
            $messages = [];

            if ($array != null)
            {
                foreach($array as $item) {
                    $message = MessageConverter::ConvertObjectToMessage($item);

                    if ($message != null)
                        $messages[] = $message;
                }
            }

            return $messages;
        }

        public static function ConvertObjectToMessage($obj) {

            if ($obj->id == null || $obj->content == null || $obj->dateTime == null)
                return;
            
            return new Message($obj->id, $obj->content, $obj->dateTime);
        }
    }
?>