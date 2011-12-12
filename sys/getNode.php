<?php

ini_set('display_errors', 1);

require_once('dbNestedTree.php');

$id = $_GET['id'];

try {
    $tree = new dbNestedTree();
}catch (Exception $e) {
    die($e->getMessage());
}

if(isset($id)) {

    $tree = $tree->getNode($id);

}

//////////format results
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
                $links .= '<span class="icons tree-hide-all" title="expand">&nbsp;</span>';
            }elseif($child === false && $depth == 0) {
                $nodeClass = 'tree-leaf';
            }elseif ($child === true) {
                $nodeClass = 'tree-closed';
                $links .= '<span class="icons tree-show-all" title="collapse">&nbsp;</span>';
            }elseif ($child === false) {
                $nodeClass = 'tree-leaf';
            }

            print<<<HTHT

        <li id="$id" class="$nodeClass">
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

                    $links = '<span class = "icons move-up" title="move up">&nbsp;</span>&nbsp;';
                    $links .= '<span class = "icons move-down" title="move down">&nbsp;</span>&nbsp;';
                    $links .= '<span class = "icons add-leaf" title="add node">&nbsp;</span>&nbsp;';
                    $links .= '<span class = "icons edit-leaf" title="edit node">&nbsp;</span>&nbsp;';
                    $links .= '<span class = "icons rem-leaf" title="remove node (without childrens)">&nbsp;</span>&nbsp;';
                    $links .= '<span class = "icons del-leaf" title="delete node (with childrens)">&nbsp;</span>&nbsp;';

                    if ($child === true && $depth == 0) {
                        $nodeClass = 'tree-open';
                        $links .= '<span class="icons tree-hide-all" title="hide tree">&nbsp;</span>';
                    }elseif($child === false && $depth == 0) {
                        $nodeClass = 'tree-leaf';
                    }elseif ($child === true) {
                        $nodeClass = 'tree-closed';
                        $links .= '<span class="icons tree-show-all" title="show tree">&nbsp;</span>';
                    }elseif ($child === false) {
                        $nodeClass = 'tree-leaf';
                    }


                    print<<<HTHT

        <li id="$id" class="$nodeClass">
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
    }

    print<<<HTHT

    </ul>

HTHT;









