<?php

function __autoload($class) {
    require_once $class.'.php';
}

class dbNestedTree Extends dbConnection
{

    private $db;


    public function __construct() {
        $this->db =& new DBConnection();
    }

/*
 * Method for getting tree
 *
 *
 *
 */
    public function getTree() {
        $sql = "SELECT node.id, node.title, node.text, node.date, node.lft, node.rgt, node.depth,
                (node.rgt - node.lft + 1) AS width FROM comments
                AS node, comments AS parent WHERE node.lft BETWEEN parent.lft AND parent.rgt
                GROUP BY node.lft HAVING depth BETWEEN 0 AND 1
                ORDER BY node.lft;";

        $result = $this->db->query($sql);
        $error_array = $this->db->errorInfo();
        if ($this->db->errorCode() != 0000) {
//            throw new Exception ("PDO Error: " . $error_array[2]);
            return false;
        }
        $tree = array();
        $i = 0;
//        print(var_dump($result->fetch()));die;
        while ($row = $result->fetch()) {
            if ($row['depth'] == 0) {
                $tree[$i]['id'] = $row['id'];
                $tree[$i]['title'] = $row['title'];
                $tree[$i]['text'] = $row['text'];
                $tree[$i]['date'] = $row['date'];
                $tree[$i]['lft'] = $row['lft'];
                $tree[$i]['rgt'] = $row['rgt'];
                if ($row['width'] >= 4) {
                    $tree[$i]['child'] = true;
                }else {
                    $tree[$i]['child'] = false;
                }
                $tree[$i]['depth'] = $row['depth'];
                $parNum = $i;
                $j = 0;
                $i++;
            }else {
                $tree[$parNum]['child_nodes'][$j]['id'] = $row['id'];
                $tree[$parNum]['child_nodes'][$j]['title'] = $row['title'];
                $tree[$parNum]['child_nodes'][$j]['date'] = $row['date'];
                $tree[$parNum]['child_nodes'][$j]['text'] = $row['text'];
                $tree[$parNum]['child_nodes'][$j]['lft'] = $row['lft'];
                $tree[$parNum]['child_nodes'][$j]['rgt'] = $row['rgt'];
                if ($row['width'] >= 4) {
                    $tree[$parNum]['child_nodes'][$j]['child'] = true;
                }else {
                    $tree[$parNum]['child_nodes'][$j]['child'] = false;
                }
                $tree[$parNum]['child_nodes'][$j]['depth'] = $row['depth'];
                $j++;
            }

        }
        return $tree;
    }

/*
 * Getting nodes with next depth
 *
 *@param int id of parent node
 */
    public function getNode($id) {

        $tree = $this->getStatTree($id);
//        print(var_dump($tree));die;
        $lft = $tree['lft'];
        $rgt = $tree['rgt'];
        $width = $tree['width'];
        $depth = $tree['depth'] + 1;

        $sql = "SELECT node.id, node.title, node.text, node.date, node.lft, node.rgt, node.depth,
                (node.rgt - node.lft + 1) AS width FROM comments
                AS node, comments AS parent WHERE node.lft BETWEEN :lft AND :rgt
                GROUP BY node.lft HAVING node.depth = :depth
                ORDER BY node.lft;";

        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->bindParam(':depth', $depth, PDO::PARAM_INT);

        $stmt->execute();

        $error_array = $stmt->errorInfo();
        if ($stmt->errorCode() != 0000) {
//            throw new Exception ("PDO Error: " . $error_array[2]);
            return false;
        }
        $tree = array();
        $i = 0;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tree[$i]['id'] = $row['id'];
            $tree[$i]['title'] = $row['title'];
            $tree[$i]['text'] = $row['text'];
            $tree[$i]['date'] = $row['date'];
            $tree[$i]['lft'] = $row['lft'];
            $tree[$i]['rgt'] = $row['rgt'];
            if ($row['width'] >= 4) {
                $tree[$i]['child'] = true;
            }else {
                $tree[$i]['child'] = false;
            }
            $tree[$i]['depth'] = $row['depth'];
            $i++;
        }
        return $tree;
    }


/*
 * Method for inserting root node.
 * Can be used alone or like part of insertNode meth.
 *
 *
 * @param title of comment
 * @param text of comment
*/
    public function insertRootNode($title, $text)
    {

        $afctRows = 0;

        $this->db->trans_begin();

        (string)$sql =  'SELECT rgt FROM comments ORDER BY rgt DESC LIMIT 1';
        $stmt =     $this->db->prepare($sql);

        $stmt->execute();
        $afctRows += $stmt->rowCount();
        //        print(var_dump($stmt->rowCount()));die;
        if($stmt->rowCount() == 0) {
            $lft = 1;
            $rgt = 2;
            $depth = 0;
        }elseif($stmt->rowCount() > 0) {
            $obj = $stmt->fetch();
            $lft = $obj['rgt'] + 1;
            $rgt = $lft + 1;
            $depth = 0;
        }
        $sql = "INSERT INTO comments (title, text, date, lft, rgt, depth) VALUES (:title, :text, NOW(), :lft, :rgt, :depth);";

        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':text', $text, PDO::PARAM_STR);
        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->bindParam(':depth', $depth, PDO::PARAM_INT);

        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $this->db->trans_commit();

        return $afctRows;

    }

/*
 * Method for inserting node root or not root.
 * Its better to use for inserting of node.
 *
 *@param integer right key of parent node
 *@param string title title for comment
 *@param string text for comment
 */
    public function insertNode($id, $title = NULL, $text = NULL)
    {

        $afctRows = 0;

        if ($id == 0) {

            $this->insertRootNode($title, $text);

        }elseif($id > 0) {

            $parentNode = $this->getStatTree($id);

            $rgt =      $parentNode['rgt'];
            $lft =      $parentNode['lft'];
            $depth =    $parentNode['depth'];

            $this->db->trans_begin();

            $sql =      'SELECT COUNT(*) FROM comments WHERE rgt = :rgt ORDER BY rgt LIMIT 1';
            $stmt =     $this->db->prepare($sql);

            $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $res = $stmt->fetch();

            if ($res[0] > 0) {

                $sql =  'UPDATE comments SET rgt = rgt + 2, lft = IF (lft > :rgt, lft + 2, lft) WHERE rgt >= :rgt;';
                $stmt = $this->db->prepare($sql);

                $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
                $stmt->execute();
                $afctRows += $stmt->rowCount();

                $sql = 'INSERT INTO comments (title, text, date, lft, rgt, depth) VALUES
                                                                    (:title, :text, NOW(), :rgt, :rgt + 1, :depth + 1);';
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':text', $text, PDO::PARAM_STR);
                $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
                $stmt->bindParam(':depth', $depth, PDO::PARAM_INT);

                $stmt->execute();
                $afctRows += $stmt->rowCount();
                $this->db->trans_commit();

                return $afctRows;

            }else {
                $this->db->trans_roll();
//                throw new Exception("Node with rgt key is not exist.");
                return false;
            }

        }else {
//            throw new Exception("Error in insertNode.");
            return false;
        }

    }

/*
 * Method for removing element from tree and keeping children.
 *
 *
 *@param integer id of removed node
 */
    public function removeFromTree($id = NULL)
    {

        $afctRows = 0;

        $this->db->trans_begin();

        $array = $this->getStatTree($id);
        //        print(var_dump($array));die;
        $lft =      $array['lft'];
        $rgt =      $array['rgt'];
        $width =    $array['width'];
        $depth =    $array['depth'];

        $sql = "DELETE FROM comments WHERE lft = :lft;";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $sql = "UPDATE comments SET lft = lft - 1, rgt = rgt - 1, depth = depth - 1 WHERE lft BETWEEN :lft AND :rgt;";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
//        $stmt->bindParam(':depth', $depth, PDO::PARAM_INT);
        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $sql = "UPDATE comments SET lft = lft - 2 WHERE lft > :rgt;";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $sql = "UPDATE comments SET rgt = rgt - 2 WHERE rgt > :rgt;";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $this->db->trans_commit();

        return $afctRows;

    }

/*
 * Method for removing branch of tree with all childrens.
 *
 *
 *
 *@param integer id of removed node
 */
    public function deleteFromTree($id = NULL)
    {

        $afctRows = 0;

        $this->db->trans_begin();

        $array = $this->getStatTree($id);

        $lft =      $array['lft'];
        $rgt =      $array['rgt'];
        $width =    $array['width'];

        $sql =      'DELETE FROM comments WHERE lft BETWEEN :lft AND :rgt;';
        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);

        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $sql =      'UPDATE comments SET rgt = rgt - :width WHERE rgt > :rgt;';
        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':width', $width, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);

        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $sql =      'UPDATE comments SET lft = lft - :width WHERE lft > :rgt;';
        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':width', $width, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);

        $stmt->execute();
        $afctRows += $stmt->rowCount();

        $this->db->trans_commit();

        return $afctRows;

    }

/*
 * Getting info about branch by right key.
 * Used in moveUp() method.
 *
 *@param integer right key of getting element
 *
 */
    function getIdRgt($rgt)
    {

        $sql =      'SELECT id, lft, rgt FROM comments WHERE lft = :rgt;';
        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $array['id'] =      $result['id'];
            $array['lft'] =     $result['lft'];
            $array['rgt'] =     $result['rgt'];

            return $array;

        }else {

            $sql =      'SELECT id, lft, rgt FROM comments WHERE rgt = :rgt;';
            $stmt =     $this->db->prepare($sql);

            $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $array['id'] =     $result['id'];
                $array['lft'] =     $result['lft'];
                $array['rgt'] =     $result['rgt'];

                return $array;

            }else {
                return false;
            }
        }
    }

/*
 * Method for moving up element through tree.
 * If element has a childrens childrens will be moved too.
 *
 *
 *
 *@param integer id of moved element
 */

    public function moveUp($id)
    {

        $afctRows = 0;

        $this->db->trans_begin();

        $array =        $this->getStatTree($id);

        $lft =          $array['lft'];
        $rgt =          $array['rgt'];
        $width =        $array['width'];

        if (($lft - 1) != 0) {
            $leftId =   $this->getIdRgt($lft - 1);
        }else {
//            throw new Exception('Cant move root node.');
            return false;
        }

        if (empty($leftId)) {
//            throw new Exception ('Cant get left neighbord.');
            return false;
        }

            $IdlftNeigh =       $leftId['id'];
            $lftLftNeigh =      $leftId['lft'];
            $rgtLftNeigh =      $leftId['rgt'];
            $wdthLftNeigh =     $rgtLftNeigh - $lftLftNeigh;

        $sql =          'UPDATE comments SET lft = - lft, rgt = - rgt WHERE lft BETWEEN :lft AND :rgt;';
        $stmt =         $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);

        $stmt->execute();
        $afctRows += $stmt->rowCount();

        if ($rgtLftNeigh > $rgt) {
            $sql =          'UPDATE comments SET  lft = lft + :width WHERE lft = :lft;';
            $stmt =         $this->db->prepare($sql);

            $stmt->bindParam(':width', $width, PDO::PARAM_INT);
            $stmt->bindParam(':lft', $lftLftNeigh, PDO::PARAM_INT);

            $stmt->execute();
            $afctRows += $stmt->rowCount();


            $sql =          'UPDATE comments SET lft = ABS(lft) - 1, rgt = ABS(rgt) - 1, depth = depth - 1
                            WHERE lft < 0 AND rgt < 0;';
            $stmt =         $this->db->prepare($sql);

            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $this->db->trans_commit();

            return $afctRows;


        }elseif($rgtLftNeigh < $rgt) {

            $sql =          'UPDATE comments SET  rgt = rgt + :width WHERE rgt = :rgt;';
            $stmt =         $this->db->prepare($sql);

            $stmt->bindParam(':width', $width, PDO::PARAM_INT);
            $stmt->bindParam(':rgt', $rgtLftNeigh, PDO::PARAM_INT);

            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $sql =          'UPDATE comments SET lft = ABS(lft) - 1, rgt = ABS(rgt) - 1, depth = depth + 1
                            WHERE lft < 0 AND rgt < 0;';
            $stmt =         $this->db->prepare($sql);

            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $this->db->trans_commit();

            return $afctRows;

        }

    }

/*
 * Method for getting info data by left key of element.
 *
 *
 *
 *@param integer left key of getting element
 */
    function getIdLft($lft)
    {

        $sql =      'SELECT id, lft, rgt FROM comments WHERE rgt = :lft;';
        $stmt =     $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $result =           $stmt->fetch(PDO::FETCH_ASSOC);
            $array['id'] =      $result['id'];
            $array['lft'] =     $result['lft'];
            $array['rgt'] =     $result['rgt'];

            return $array;

        }else {
            $sql =      'SELECT id, lft, rgt FROM comments  WHERE lft = :lft;';

            $stmt =     $this->db->prepare($sql);

            $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $result =           $stmt->fetch(PDO::FETCH_ASSOC);
                $array['id'] =      $result['id'];
                $array['lft'] =     $result['lft'];
                $array['rgt'] =     $result['rgt'];

                return $array;

            }else {
                return false;
            }
        }
    }

/*
 * Move element down through tree.
 * If element has a childrens they will be moved too.
 *
 *@param integer id of moved element
 */
    public function moveDown($id)
    {

        $afctRows = 0;

        $this->db->trans_begin();

        $array = $this->getStatTree($id);

        $lft =          $array['lft'];
        $rgt =          $array['rgt'];
        $width =        $array['width'];

        $leftId =   $this->getIdLft($rgt + 1);

        if (empty($leftId)) {
//            throw new Exception ('Cant get left neighbord.');
            return false;
        }

            $IdlftNeigh =   $leftId['id'];
            $lftRgtNeigh =  $leftId['lft'];
            $rgtRgtNeigh =  $leftId['rgt'];
            $wdthLftNeigh = $rgtRgtNeigh - $lftRgtNeigh;

        $sql =          'UPDATE comments SET lft = - lft, rgt = - rgt WHERE lft BETWEEN :lft AND :rgt;';
        $stmt =         $this->db->prepare($sql);

        $stmt->bindParam(':lft', $lft, PDO::PARAM_INT);
        $stmt->bindParam(':rgt', $rgt, PDO::PARAM_INT);
        $stmt->execute();
        $afctRows += $stmt->rowCount();

        if ($lftRgtNeigh > $lft) {
            $sql =          'UPDATE comments SET  lft = lft - :width WHERE lft = :lft;';
            $stmt =         $this->db->prepare($sql);

            $stmt->bindParam(':width', $width, PDO::PARAM_INT);
            $stmt->bindParam(':lft', $lftRgtNeigh, PDO::PARAM_INT);
            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $sql =          'UPDATE comments SET lft = ABS(lft) + 1, rgt = ABS(rgt) + 1, depth = depth + 1
                            WHERE lft < 0 AND rgt < 0;';
            $stmt =         $this->db->prepare($sql);

            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $this->db->trans_commit();

            return $afctRows;


        }elseif($lftRgtNeigh < $lft) {

            $sql =          'UPDATE comments SET  rgt = rgt - :width WHERE rgt = :rgt;';
            $stmt =         $this->db->prepare($sql);

            $stmt->bindParam(':width', $width, PDO::PARAM_INT);
            $stmt->bindParam(':rgt', $rgtRgtNeigh, PDO::PARAM_INT);
            $stmt->execute();

            $afctRows += $stmt->rowCount();

            $sql =          'UPDATE comments SET lft = ABS(lft) + 1, rgt = ABS(rgt) + 1, depth = depth - 1
                             WHERE lft < 0 AND rgt < 0;';
            $stmt =         $this->db->prepare($sql);

            $stmt->execute();
            $afctRows += $stmt->rowCount();

            $this->db->trans_commit();

            return $afctRows;

        }


    }

/*
 *Method for getting data for edit node
 *
 *
 *@param integer id of edited node
 */
    public function getNodeEdit($id = NULL)
    {

        if($id != NULL) {

            $this->db->trans_begin();

            $sql = "SELECT id, title, text FROM comments WHERE id = :id;";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $this->db->trans_commit();

                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $array['id'] =      $result['id'];
                $array['title'] =   $result['title'];
                $array['text'] =    $result['text'];

                return $array;


            }else {
                $this->db->trans_roll();
                return false;
            }

        }

    }

/*
 *
 * Method for update node
 *
 *
 *@param id int updated id
 *@param string title for update
 *@param string text for update
 */

    public function updateNode($id, $title, $text) {

        if($id != NULL) {

            $afctRows = 0;

//            print(var_dump($text));die;

            $this->db->trans_begin();

            if ($this->getStatTree($id)) {

                $sql = 'UPDATE comments SET title = :title, text = :text WHERE id = :id;';
                $stmt = $this->db->prepare($sql);

                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt->bindParam(':text', $text, PDO::PARAM_STR);
                $stmt->execute();
                $afctRows += $stmt->rowCount();

                if ($stmt->rowCount() > 0) {

                    $this->db->trans_commit();

                    return $afctRows;

                }else {
                    $this->db->trans_roll();
                    return false;
                }

            }else {
                $this->db->trans_roll();
                return false;
            }

        }

    }

/*
 * Method for getting info data for element by id.
 *
 *
 *
 *@param integer id of getting element
 */

    public function getStatTree($id = NULL) {

        $sql =  'SELECT lft, rgt, depth, (rgt - lft + 1) AS treeWidth FROM comments WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $result =           $stmt->fetch(PDO::FETCH_ASSOC);
            $array['lft'] =     $result['lft'];
            $array['rgt'] =     $result['rgt'];
            $array['width'] =   $result['treeWidth'];
            $array['depth'] =   $result['depth'];

            return $array;

        }else {
            return false;
//            throw new Exception ("Error at getStatTree func.");
        }

    }





}
