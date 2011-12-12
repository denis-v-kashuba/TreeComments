<?php

print<<<HTHT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>binary tree - comments</title>
	<meta http-equiv="Content-Language" content="ru" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="stylesheet" type="text/css" href="sys/css/style.css" />

    <script type="text/javascript" src="sys/js/jquery-1.7.min.js"></script>
    <script type="text/javascript" src="sys/js/init.js"></script>

</head>

HTHT;

print<<<HTHT

    <body>

HTHT;


ini_set('display_errors', 1);

define("BASE_DIR",dirname(__FILE__));

require_once(BASE_DIR . '/sys/dbNestedTree.php');

//////////////getting obj of tree class
try {
    $tree = new dbNestedTree();
}catch (Exception $e) {
    die($e->getMessage());
}

/////////////// this is for manual manipulate
//try {
    //daa nodes
//    echo "<pre>"; var_dump($tree->insertRootNode('test1', 'text1')); echo "</pre>";die;
//    echo "<pre>"; var_dump($tree->insertNode(133, 'testX', 'testX')); echo "</pre>";die;
    //removing node
//    echo "<pre>"; var_dump($tree->removeFromTree(132)); echo "</pre>";die;
    //deleting node
//    echo "<pre>"; var_dump($tree->deleteFromTree(129)); echo "</pre>";die;
    //move up
//    echo "<pre>"; var_dump($tree->moveUp(139)); echo "</pre>";die;
      //move down
//    echo "<pre>"; var_dump($tree->moveDown(132)); echo "</pre>";die;
    //get tree
//    echo "<pre>"; var_dump($tree->getTree()); echo "</pre>";die;
    //get node
//    echo "<pre>"; var_dump($tree->getNode(131, 1)); echo "</pre>";die;
    //info about tree
//echo "<pre>"; var_dump($tree->getStatTree(131)); echo "</pre>";die;

//}catch (Exception $e) {
//    die($e->getMessage());
//}


    print<<<HTHT

    <div id="addComment">
        <span>Add comment</span>
     <form action="" name="add_comment" enctype="text/plain" >
        <p class="hidden"></p>
        <label>Title :
            <input name="title" id="title" size="30" type="text" tabindex="1" />
        </label>

        <label>Text :
            <textarea name="text" id="text" rows="5" cols="23" tabindex="2"></textarea>
        </label>

        <input name="submit" type="submit" class="submit" tabindex="3" accesskey=""/>

     </form>

    </div>

HTHT;


    print<<<HTHT

    <div id="editComment">
        <span>Edit comment</span>


    </div>

HTHT;




try {
    $tree = $tree->getTree();
}catch (Exception $e) {
    die($e->getLine());
}

    print<<<HTHT

    <div id="tree">
        <ul id="tree-main">
HTHT;

//    print(var_dump($tree));die;

////////////////// formate tree for output
    if (!empty($tree)) {
        foreach ($tree as $key => $val) {

            $id = $val['id'];
            $child = $val['child'];

            $level = '';
            $depth = $val['depth'];
            $level = str_pad($level, $val['depth'], ".",STR_PAD_LEFT);

            $title = $val['title'];
            $text = $val['text'];
            $date = $val['date'];

            $lft = $val['lft'];
            $rgt = $val['rgt'];

            $links = '<span class = "move-up" title="move up">&nbsp;</span>&nbsp;';
            $links .= '<span class = "move-down" title="move down">&nbsp;</span>&nbsp;';
            $links .= '<span class = "add-leaf" title="add node">&nbsp;</span>&nbsp;';
            $links .= '<span class = "edit-leaf" title="edit node">&nbsp;</span>&nbsp;';
            $links .= '<span class = "rem-leaf" title="remove node (without childrens)">&nbsp;</span>&nbsp;';
            $links .= '<span class = "del-leaf" title="delete node (with childrens)">&nbsp;</span>&nbsp;';

            if ($child === true && $depth == 0) {
                $nodeClass = 'tree-open';
                $links .= '<span class="tree-hide-all" title="collapse">&nbsp;</span>';
            }elseif($child === false && $depth == 0) {
                $nodeClass = 'tree-leaf';
            }elseif ($child === true) {
                $nodeClass = 'tree-closed';
                $links .= '<span class="tree-show-all" title="expand">&nbsp;</span>';
            }elseif ($child === false) {
                $nodeClass = 'tree-leaf';
            }

        print<<<HTHT

        <li id="item_$id" class="$nodeClass">
        <div class="tree-item">
            <p class="tree-level">$level</p>
            <!--<span class="tree-icon">&nbsp;</span>-->
            <p class="tree-data">
                <span class="tree-date">$date</span>
                <span class = "tree-title">$title</span>
                <span class="tree-text">$text</span>
            </p>
                <p class = "tree-links">$links</p>
                <p class="tree-info">Node info: id=$id&nbsp;left=$lft&nbsp;right=$rgt&nbsp;depth=$depth&nbsp;</p>
        </div>

HTHT;

            if(isset($val['child_nodes'])) {

        print<<<HTHT

        <ul>

HTHT;

             foreach ($val['child_nodes'] as $keyN => $valN ) {

                 $id = $valN['id'];
                 $child = $valN['child'];

                 $level = '';
                 $depth = $valN['depth'];
                 $level = str_pad($level, $valN['depth'], ".",STR_PAD_LEFT);

                 $title = $valN['title'];
                 $text = $valN['text'];
                 $date = $val['date'];

                 $lft = $valN['lft'];
                 $rgt = $valN['rgt'];

                 //           print(var_dump($val['depth']));die;

                 $links = '<span class = "move-up" title="move up">&nbsp;</span>&nbsp;';
                 $links .= '<span class = "move-down" title="move down">&nbsp;</span>&nbsp;';
                 $links .= '<span class = "add-leaf" title="add node">&nbsp;</span>&nbsp;';
                 $links .= '<span class = "edit-leaf" title="edit node">&nbsp;</span>&nbsp;';
                 $links .= '<span class = "rem-leaf" title="remove node (without childrens)">&nbsp;</span>&nbsp;';
                 $links .= '<span class = "del-leaf" title="delete node (with childrens)">&nbsp;</span>&nbsp;';

                 if ($child === true && $depth == 0) {
                     $nodeClass = 'tree-open';
                     $links .= '<span class="tree-hide-all" title="collapse">&nbsp;</span>';
                 }elseif($child === false && $depth == 0) {
                     $nodeClass = 'tree-leaf';
                 }elseif ($child === true) {
                     $nodeClass = 'tree-closed';
                     $links .= '<span class="tree-show-all" title="expand">&nbsp;</span>';
                 }elseif ($child === false) {
                     $nodeClass = 'tree-leaf';
                 }


        print<<<HTHT

        <li id="item_$id" class="$nodeClass">
        <div class="tree-item">
            <p class="tree-level">$level</p>
            <!--<span class="tree-icon">&nbsp;</span>-->
            <p class="tree-data">
                <span class = "tree-title">$title</span>
                <span class = "tree-text">$text</span>
            </p>
                <p class = "tree-links">$links</p>
            <p class="tree-info">Node info: id = $id&nbsp;left = $lft&nbsp;right = $rgt&nbsp;depth = $depth&nbsp;</p>
        </div>

HTHT;


        print<<<HTHT

        </li>

HTHT;

             }

        print<<<HTHT

        </ul>

HTHT;

            }

        print<<<HTHT

        </li>

HTHT;

        }
    }else {

        $links = '<span class = "add-leaf" title="add node">&nbsp;</span>&nbsp;';

        print<<<HTHT

        <li id="0" class="emptyTree">
        <div class="tree-item">
            <!--<span class="tree-icon">&nbsp;</span>-->
            <p class="tree-data">
                <span class = "tree-title">Tree is empty, please add comment.</span>
            </p>
                <p class = "tree-links">$links</p>
        </div>

HTHT;


        print<<<HTHT

        </li>

HTHT;

    }


        print<<<HTHT
        </ul>
    </div>
HTHT;

        print<<<HTHT

        </body>
        </html>

HTHT;


