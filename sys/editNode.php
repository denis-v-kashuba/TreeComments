<?php

ini_set('display_errors', 1);

require_once('dbNestedTree.php');

try {
    $tree = new dbNestedTree();
}catch (Exception $e) {
    die($e->getMessage());
}


$id = $_GET['id'];

if(isset($id)) {

    $array = $tree->getNodeEdit($id);

}else {
    $form = null;
}

$id = 'item_'.$array['id'];
$oldId = $array['id'];

$title = $array['title'];

$text = $array['text'];

print"<form action='' id='edit_comment' name='edit_comment' enctype='text/plain' >
                    <span>Edit comment id = $oldId :</span></br>
                    <p class='hidden'><input name='id' id='id_edit' type='hidden' value='$id' /></p>

                    <label >Title :
                        <input name='title' id='title_edit' size='30' type='text' value='$title'
                                tabindex=\"1\" />
                    </label>

                    <label>Text :
                        <textarea name='text' id='text_edit' rows='5' cols='23'
                            tabindex='2'>$text</textarea>
                    </label>

                    <input name='submit' type='submit' class='edit_submit' tabindex='3' />

                </form>
                ";