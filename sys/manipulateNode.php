<?php

ini_set('display_errors', 1);

require_once('dbNestedTree.php');


//////// getting obj of tree class
try {
    $tree = new dbNestedTree();
}catch (Exception $e) {
    die($e->getMessage());
}

$id = $_GET['id'];
$move = $_GET['func'];

//////////////define method for proceed with tree
if(isset($id) && $move == 'moveUp') {

    $tree->moveUp($id);
    $tree = $tree->getTree();

}elseif(isset($id) && $move == 'moveDown') {

    $tree->moveDown($id);
    $tree = $tree->getTree();

}elseif(isset($id) && $move == 'remove') {

    $tree->removeFromTree($id);
    $tree = $tree->getTree();

}elseif(isset($id) && $move == 'delete') {

    $tree->deleteFromTree($id);
    $tree = $tree->getTree();

}elseif($move == 'addNode') {

    $title = $_GET['title'];
    $text = $_GET['text'];

    $tree->insertNode($id, $title, $text);
    $tree = $tree->getTree();

}elseif(isset($id) && $move == 'updNode') {

    $title = $_GET['title'];
    $text = $_GET['text'];

    $tree->updateNode($id, $title, $text);
    $tree = $tree->getTree();

}else {

    $tree = null;

}


//////formate results here
    print<<<HTHT

        <ul>

HTHT;

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
                <span class="tree-date">$date</span>
                <span class="tree-title">$title</span>
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
                <span class="tree-date">$date</span>
                <span class = "tree-title">$title</span>
                <span class = "tree-text">$text</span>
            </p>
                <p class = "tree-links">$links</p>
                <p class="tree-info">Node info: id=$id&nbsp;left=$lft&nbsp;right=$rgt&nbsp;depth=$depth&nbsp;</p>
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
}else{

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

HTHT;


