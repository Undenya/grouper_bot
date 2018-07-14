<?php
/**
 * Created by PhpStorm.
 * User: UnDenya
 * Date: 11.07.2018
 * Time: 19:46
 */

class C_Bot
{
    private static $instance;
    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;
        return self::$instance;
    }

    public static $db;
    var $phone;
    function startUp($MadelineProto)
    {
        echo 'Bot started.' . PHP_EOL;
        $offset = 0;
        $my_bot = $MadelineProto->get_self();
        $my_id = $my_bot["id"];
        while (true) {
            $updates = $MadelineProto->get_updates(['offset' => $offset, 'limit' => 3, 'timeout' => 1]); // Just like in the bot API, you can specify an offset, a limit and a timeout
            foreach ($updates as $update) {
                $offset = $update['update_id'] + 1; // Just like in the bot API, the offset must be set to the last update_id
                //Parse $update['update'], that is an object of type Update

                if (isset($update['update']['message']['from_id'])) {
                    //var_dump($update);
                    $db = new MysqliDb("localhost", "users_bot", "6AVwnhiJqnwnD4fg", "users_db",
                        3306, "utf8mb4");

                    echo "User_ID: " . $update['update']['message']['from_id'] . "\n";
                    $user_id = $update['update']['message']['from_id'];
                    if($user_id == $my_id)
                    {
                        continue;
                    }

                    if(isset($update['update']['message']["id"])) {
                        echo "Message_ID: ".$update['update']['message']["id"] . "\n";
                        $message_id = $update['update']['message']["id"];
                    }

                    if(isset($update['update']['message']["message"])) {
                        $user_message = $update['update']['message']["message"];
                        echo "Message: ".$user_message . "\n";

                    }
                    else
                    {
                        $data_update = ["chat_id" => null];
                        $db->where("user_id", $user_id);
                        $db->update("users", $data_update);
                        continue;
                    }


                    $db->where("user_id", $user_id);
                    $users = $db->getOne("users");
                    if($users != null && $users["chat_id"] != null)
                    {
                        try
                        {
                            if(!isset($update["update"]["message"]["to_id"]["chat_id"]))
                            {
                                $MadelineProto->messages->forwardMessages(['from_peer' => $user_id,
                                    'id' => [$message_id], 'to_peer' => "chat#".$users["chat_id"], ]);
                            }
                        }
                        catch (Exception $e)
                        {
                            echo $e->getMessage();
                        }
                        continue;
                    }
                    else
                    {
                        if($users["chat_id"] == null)
                        {
                            try
                            {
                                $user_info = $MadelineProto->get_info($user_id);
                                if(isset($user_info["User"]["first_name"]))
                                {
                                    $first_name = $user_info["User"]["first_name"];
                                }
                                else
                                {
                                    $first_name = $user_id;
                                }
                                echo $first_name. "\n";
                                $chat = $MadelineProto->messages->createChat(['users' => [$user_id], 'title' => $first_name."_KeyProxy_Support", ]);
                            }
                            catch (Exception $e)
                            {
                                echo $e->getMessage();
                            }
                        }

                        $data["user_id"] = $user_id;

                        if(isset($chat["chats"][0]["id"]))
                        {
                            if($users["chat_id"] == null)
                            {
                                try
                                {
                                    $MadelineProto->messages->forwardMessages(['from_peer' => $user_id,
                                        'id' => [$message_id], 'to_peer' => "chat#".$chat["chats"][0]["id"], ]);
                                }
                                catch (Exception $e)
                                {
                                    echo $e->getMessage();
                                }

                            }
                            $data["chat_id"] = $chat["chats"][0]["id"];
                        }

                        if($users["user_id"] == null)
                        {
                            $db->insert("users", $data);
                        }
                        else
                        {
                            $db->where("user_id", $users["user_id"]);
                            $db->update("users", $data);
                        }
                    }
                }
            }
        }
    }
}