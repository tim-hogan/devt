<?php
/*
*/
/* Error defines */
define('WORDOC_FILE_ALREADY_OPEN','1');
define('WORDOC_FILE_DOES_NOT_EXIST','2');
define('WORDOC_CANNOT_OPEN_FILE','3');
define('WORDOC_CANNOT_FIND_DOCUMENT','4');
define('WORDOC_CANNOT_CREATE_DIRECTORY','5');
define('WORDOC_CANNOT_FIND_BODY','6');
define('WORDOC_FILE_NOT_OPEN','7');

define('WORDDOC_DOCUMENT','w:document');
define('WORDDOC_BODY','w:body');
define('WORDDOC_PARAGRAPH','w:p');
define('WORDDOC_TEXT','w:t');
define('WORDDOC_TABLE','w:tbl');
define('WORDDOC_TABLE_ROW','w:tr');
define('WORDDOC_TABLE_CELL','w:tc');
define('WORDDOC_DRAWING','w:drawing');

class WordDoc
{
    private $_workingdir = null;
    private $_docName;
    private $_fileOpen;
    private $_doc;
    private $_rels;
    private $_document;
    private $_body;
    private $_properties;
    private $_templates;

    private $_debug;

    public $lasterror;

    //Error defines
    private $_errortext = [
        0=>'No error',
        1=>'Canot open as file already open',
        2=>'Cannot find or open file',
        3=>'Cannot open file',
        4=>'Document element not found',
        5=>'Cannot create a working directory',
        6=>'Body element not found',
        7=>'No file open'
    ];

    function __construct($working_dir=null)
    {
        $this->lasterror = 0;
        $this->_fileOpen = false;
        $this->debug = false;
        $this->_templates = array();
        if ($working_dir)
        {
            $this->_workingdir = $working_dir;
        }
    }

    function __destruct()
    {
        $this->close();
    }

    private function var_error_log( $object=null ,$text='')
    {
        ob_start();                    // start buffer capture
        var_dump( $object );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( "{$text} {$contents}" );        // log contents of the result of var_dump( $object )
    }

    private function createRandomInt($length=6)
    {
        $val = '';
        for ($c=0;$c<($length/4);$c++)
            $val .= sprintf("%04d",rand(0,9999));
        $val = intval(substr($val,0,$length));
        return $val;
    }

    private function deBug($t)
    {
        if ($this->_debug)
            error_log($t);
    }

    private function stringContains($src,$what,$ingnorecase=true)
    {
        if ($ingnorecase)
        {
            $src2 = strtoUpper($src);
            $what2 = strtoUpper($what);
        }
        else
        {
            $src2 = $src;
            $what2 = $what;
        }
        if (strpos($src2, $what2) !== false) {
            return true;
        }
        return false;
    }

    private function stringStarts($src,$what,$ingnorecase=true)
    {
        if ($ingnorecase)
        {
            $src2 = strtoUpper($src);
            $what2 = strtoUpper($what);
        }
        else
        {
            $src2 = $src;
            $what2 = $what;
        }
        $v = strpos($src2, $what2);
        if ($v !== false && $v == 0) {
            return true;
        }
        return false;
    }

    private function filesInDir($dir)
    {
        //Returns of all files in the directory including the directory
        $ret = array();
        $files = scandir($dir);
        foreach($files as $file)
        {
            if (is_dir("{$dir}/{$file}"))
            {
                if ($file != "." && $file != "..")
                {
                    $list = $this->filesInDir("{$dir}/{$file}");
                    $ret = array_merge($ret,$list);
                }
            }
            else
            {
                array_push($ret,"{$dir}/{$file}");
            }
        }
        return $ret;
    }

    public function setDebug()
    {
        $this->_debug = true;
    }

    public function resetDebug()
    {
        $this->_debug = false;
    }

    public function lastErrorText()
    {
        return $this->_errortext[$this->lasterror];
    }

    private function delete_files($target)
    {
        //$this->deBug("WordDoc::delete_files start with {$target}");
        if(is_dir($target))
        {
            $files = scandir($target);
            foreach($files as $file )
            {
                if ($file != "." && $file != "..")
                    $this->delete_files( $target . "/" . $file );
            }

            if (!rmdir( $target ) )
            {
                error_log("WordDoc::delete_files Error removing directory {$target}");
            }
        }
        elseif(is_file($target))
        {
            chmod ($target,0777);
            if (!unlink( $target ) )
                error_log("WordDoc::delete_files Error deleting file {$target}");
        }

    }
    private function removeWorking()
    {
        if (file_exists($this->_workingdir))
        {
            $this->delete_files($this->_workingdir);
        }
    }

    private function zipit($dir,$dest)
    {
        $zip = new ZipArchive();
        $zip->open($dest, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = $this->filesInDir($dir);
        foreach ($files as $file)
        {
            $name = substr($file, strlen($dir) + 1);
            if (!$zip->addFile($file,$name) )
                error_log("Error adding file {$file}");
        }
        $zip->close();
    }

    private function unzip($strFilename)
    {
        $this->deBug("WordDoc:unzip {$strFilename}");
        $zip = new ZipArchive;
        $res = $zip->open($strFilename);
        if ($res === TRUE)
        {
            $this->deBug("WordDoc:unzip attempt to extract to: {$this->_workingdir}");
            $rslt = $zip->extractTo($this->_workingdir);
            $zip->close();
            return $rslt;
        }
        else
            return false;
    }

    public function open($strDocName)
    {
        $this->deBug("WordDoc::open {$strDocName}");
        $this->lasterror = 0;
        if ($this->_fileOpen)
        {
            $this->lasterror = intval(WORDOC_FILE_ALREADY_OPEN);
            $this->deBug("WordDoc:open Cannot open as file already open");
            return false;
        }

        if (!file_exists($strDocName))
        {
            $this->lasterror = intval(WORDOC_FILE_DOES_NOT_EXIST);
            $this->deBug("WordDoc:open Cannot open as file does not exsit {$strDocName}");
            return false;
        }
        $this->_docName = $strDocName;
        $dtNow = new DateTime();
        if (!$this->_workingdir)
        {
            $this->_workingdir = getcwd() . "/WordDoc_Temp" . $dtNow->getTimestamp();
        }
        if (!file_exists($this->_workingdir))
        {
            $this->deBug("WordDoc:open Creating working directory {$this->_workingdir}");
            if (!mkdir($this->_workingdir, 0777, true) )
            {
                $this->lasterror = intval(WORDOC_CANNOT_CREATE_DIRECTORY);
                return false;
            }
            $this->deBug("WordDoc:open Working directory created");
        }

        if (!$this->unzip($strDocName))
        {
            $this->lasterror = intval(WORDOC_CANNOT_OPEN_FILE);
            $this->removeWorking();
            $this->deBug("WordDoc:open Cannot unzip word file {$strDocName}");
            return false;
        }

        $this->_fileOpen = true;

        $this->_doc = new DOMDocument();
        if ($this->_doc->load("{$this->_workingdir}/word/document.xml") )
        {
            if (!$this->_document = $this->findNthElement(WORDDOC_DOCUMENT,1,$this->_doc) )
            {
                $this->lasterror = intval(WORDOC_CANNOT_FIND_DOCUMENT);
                $this->close();
                $this->deBug("WordDoc:open Cannot find document element {$strDocName}");
                return false;
            }
            if (!$this->_body = $this->findNthElement(WORDDOC_BODY,1,$this->_document) )
            {
                $this->lasterror = intval(WORDOC_CANNOT_FIND_DOCUMENT);
                $this->close();
                $this->deBug("WordDoc:open Cannot find document element {$strDocName}");
                return false;
            }

            //Search for templates
            $l = $this->_body->childNodes;
            if($l->length > 0)
            {
                foreach($l as $k)
                {
                    $t = $this->getNodeText($k);
                    if ($this->stringStarts($t,"template"))
                    {
                        $name = substr($t,8,strlen($t)-8);
                        $newnode = $k->cloneNode(true);
                        $this->_templates[$name] = $newnode;
                    }
                }
            }
            $this->var_error_log($this->_templates);
        }

        $this->_rels = new DOMDocument();
        if ($this->_rels->load("{$this->_workingdir}/word/_rels/document.xml.rels") )
        {
            $this->deBug("WordDoc::open rels file opened and loaded");
        }

        return true;
    }

    public function close()
    {
        $this->deBug("WordDoc::close");
        $this->lasterror = 0;
        //Remove the working
        $this->removeWorking();
        $this->_fileOpen = false;
        $this->_doc = null;
        $this->_document = null;
        $this->_body = null;
        $this->_properties = null;
    }

    public function saveAs($strNewDoc)
    {
        $this->lasterror = 0;
        $this->_doc->save("{$this->_workingdir}/word/document.xml");
        $this->_rels->save("{$this->_workingdir}/word/_rels/document.xml.rels");
        $this->zipit($this->_workingdir,$strNewDoc);
    }

    private function outputNodes($node,$level=0)
    {
        echo "<tr>";
        for ($i = 0; $i < $level;$i++)
            echo "<td></td>";
        $colspan = 20-$level;
        echo "<td colspan='{$colspan}'>";
        echo $node->nodeName;
        echo "<td>";
        echo "</tr>";

        $children = $node->childNodes;
        foreach($children as $child)
            $this->outputNodes($child,$level+1);
    }

    public function dumpAllToHTML()
    {
        if ($this->_fileOpen)
        {
            echo "<table>";
            $this->outputNodes($this->_doc,0);
            echo "</table>";
        }
    }

    public function dumpNodeToLog($node,$level=0)
    {
        if ($node)
        {
            if ($level == 0)
                error_log("Dump of DOMNode");
            $str = '';
            for ($i = 0; $i < $level;$i++)
                $str .= "    ";
            $str .= $node->nodeName;
            if ($node->hasAttributes())
            {
                error_log($str);
                $str='';
                for ($i = 0; $i < $level;$i++)
                    $str .= "    ";

                $attr = $node->attributes;
                for ($idx = 0; $idx < $attr->length; $idx++)
                {
                    $str .= "  ";
                    $str .= $attr->item($idx)->nodeName . "=>" . $attr->item($idx)->nodeValue;
                    error_log($str);
                    $str='';
                    for ($i = 0; $i < $level;$i++)
                        $str .= "    ";
                }
            }
            $str .= "   =>" . $node->nodeValue;
            error_log($str);

            $children = $node->childNodes;
            if ($children)
            {
                if ($children->length > 0)
                {
                    foreach($children as $child)
                        $this->dumpNodeToLog($child,$level+1);
                }
            }
        }
    }

    public function getTemplate($name)
    {
        if (isset($this->_templates[$name]))
        {
            return $this->_templates[$name];
        }
        return null;
    }

    public function findNthElement($typeElement,$n=1,$src=null)
    {
        /************************************************
         This function returns the nTh child elemnt of type defined by typeElement
         This is 1 relative
         Functions returns null if element not found.
        */
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $cnt = 1;
        $nodes = $src->childNodes;
        foreach ($nodes as $nd)
        {
            if ($nd->nodeName == $typeElement)
            {
                if ($cnt == $n)
                    return $nd;
                $cnt++;
            }
        }
        return null;
    }

    public function findAllElementsOfType($typeElement,&$list,$startnode=null)
    {
        $start = $startnode;
        if ($start == null)
            $start = $this->_body;
        if ($start->nodeName == $typeElement)
            array_push($list,$start);
        if ($l = $start->childNodes )
        {
            foreach ($l as $n)
            {
                $this->findAllElementsOfType($typeElement,$list,$n);
            }
        }
    }

    public function findFirstDownstreamElement($typeElement,$sartnode=null)
    {
        $start = $sartnode;
        if ($start == null)
            $start = $this->_body;
        if ($start->nodeName == $typeElement)
            return $start;
        else
        {
            if ($l = $start->childNodes )
            {
                foreach ($l as $n)
                {
                    $rslt = $this->findFirstDownstreamElement($typeElement,$n);
                    if ($rslt)
                        return $rslt;
                }
            }
        }
        return null;
    }

    public function findDocElement($typeElement,$n=1)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        return $this->findNthElement($typeElement,$n,$this->_body);
    }

    public function findNthElementWith($typeElement,$n,$withsub,$startnode=null)
    {
        //Finds the root element that contains as a child sub elementy
        //$n is pone relative

        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }

        $start = $startnode;
        if ($start == null)
            $start = $this->_body;

        $this->lasterror = 0;
        $cnt = 1;
        $nodes = $start->childNodes;
        foreach ($nodes as $nd)
        {
            if ($nd->nodeName == $typeElement)
            {
                $f1 = $this->findFirstDownstreamElement($withsub,$nd);
                if ($f1)
                {
                    if ($cnt == $n)
                        return $nd;
                    $cnt++;
                }
            }
        }
        return null;
    }

    public function findElementWithAttribute($typeElement,$attName,$attValue,$startnode=null)
    {
        //Finds the element of type $typeEelemnt that has attribute $attName of value $attValue
        //Does not recurse

        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }

        $start = $startnode;
        if ($start == null)
            $start = $this->_body;

        $this->lasterror = 0;
        $cnt = 1;
        $nodes = $start->childNodes;
        foreach ($nodes as $nd)
        {
            if ($nd->nodeName == $typeElement)
            {
                for($ix = 0;$ix < $nd->attributes->length;$ix++)
                {
                    if ($nd->attributes->item($ix)->nodeName == $attName && $nd->attributes->item($ix)->nodeValue == $attValue)
                        return $nd;
                }
            }
        }
        return null;
    }

    public function allChildren($node = null)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = null;
        if (!$node)
            $src = $this->_body;
        else
            $src = $node;
        return $src->childNodes;
    }

    public function deleteAllChildren($node)
    {
        if ($node)
        {
            $children = $node->childNodes;
            foreach($children as $child)
            {
                $this->deleteParagraph($child);
            }
        }
    }

    public function findParagraphWithText($serachtext,$node = null)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = null;
        if (!$node)
            $src = $this->_body;
        else
            $src = $node;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == WORDDOC_PARAGRAPH && $n->nodeValue == $serachtext)
                return $n;
        }
        return null;
    }

    public function findParagraphWhichIncludesText($serachtext,$node = null)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = null;
        if (!$node)
            $src = $this->_body;
        else
            $src = $node;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == WORDDOC_PARAGRAPH && strpos($n->nodeValue, $serachtext) !== false)
                return $n;
        }
        return null;
    }

    public function replaceParagraphWithText($serachtext,$newtext,$node = null)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = null;
        if (!$node)
            $src = $this->_body;
        else
            $src = $node;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == WORDDOC_PARAGRAPH)
            {
                //Look for r tag
                $r = $this->findNthElement("w:r",1,$n);
                if ($r)
                {
                    $t = $this->findNthElement("w:t",1,$r);
                    if ($t)
                    {
                        if ($t->nodeValue == $serachtext)
                        {
                            $t->nodeValue = $newtext;
                            $this->deBug("WordDoc:replaceParagraphWithText New node value = {$t->nodeValue}");
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function deleteParagraph($para)
    {
        $p = $para->parentNode;
        if($p)
            return $p->removeChild($para);
        return null;
    }

    public function deleteParagraphWithText($serachtext)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = $this->_body;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == WORDDOC_PARAGRAPH && strpos($n->nodeValue, $serachtext) !== false)
            {
                $n->parentNode->removeChild($n);
                return true;
            }
        }
        return false;
    }

    public function replaceText($serachtext,$newtext,$node = null)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = null;
        if (!$node)
            $src = $this->_body;
        else
            $src = $node;

        $this->deBug("WordDoc:replaceText Start");
        if ($src->nodeName == WORDDOC_TEXT)
        {
            $txt = $src->nodeValue;
            $this->deBug("WordDoc:replaceText found text node text = {$txt} searchtext = {$serachtext}");

            if ($pos = strpos($txt,$serachtext) !== false)
            {
                $this->deBug("WordDoc:replaceText found searchtext = {$serachtext}");
                $pos = strpos($txt,$serachtext);
                $v =  substr($txt,0,$pos) .  $newtext . substr($txt,$pos+strlen($serachtext),strlen($txt)-($pos+strlen($serachtext)));
                $src->nodeValue = $v;
                $this->deBug("WordDoc:replaceText replace searchtext with {$newtext}");
                return true;
            }
        }

        if ($src->hasChildNodes())
        {
            $l = $src->childNodes;
            if($l->length > 0)
            {
                foreach ($l as $k)
                {
                    $this->deBug("WordDoc:replaceText Recursive call with a node of name {$k->nodeName}");
                    if ($this->replaceText($serachtext,$newtext,$k) )
                        return true;
                }
            }
        }

        return false;
    }

    public function getTextNode($node)
    {
        if ($node->nodeName == "#text")
        {
            return $node;
        }
        $l = $node->childNodes;
        if($l->length > 0)
        {
            foreach ($l as $k)
            {
                $v = $this->getTextNode($k);
                if ($v)
                   return $v;
            }
        }
        return null;
   }

    public function getNodeText($node)
    {
        if ($node->nodeName == "#text")
        {
            return $node->nodeValue;
        }
        $l = $node->childNodes;
        if($l->length > 0)
        {
            foreach ($l as $k)
            {
                $v = $this->getNodeText($k);
                if (strlen($v) > 0)
                    return $v;
            }
        }
        return '';
    }

    public function newText($node,$text)
    {
        $e = $this->findFirstDownstreamElement("#text",$node);
        if ($e)
        {
            $e->nodeValue = $text;
            return true;
        }
        return false;
    }

    public function insertParagraphBefore($p,$before=null)
    {
        $this->deBug("WordDoc::insertParagraphBefore");

        if ($before)
        {
            return $this->_body->insertBefore($p,$before);
        }
        else
        {
            return $this->_body->appendChild($p);
        }
    }

    public function insertParaClonedFromBefore($clonedfrom,$before,$newtext='',$replacetext='')
    {
        //Replace text if set is the text to reaplcae with newtext.
        //Newtext on its own creates new text for para
        if ($clonedfrom)
        {
            $p1 = $clonedfrom->cloneNode(true);
            if (strlen($replacetext) > 0)
                $this->replaceText($replacetext,$newtext,$p1);
            else
            if (strlen($newtext) > 0)
                $this->newText($p1,$newtext);
            $this->insertParagraphBefore($p1,$before);
            return $p1;
        }
        return null;
    }

    /*******************************************************************
        Relationships
     */

    public function getRelationshipTarget($id)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = $this->_rels;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == 'Relationships')
            {
                $rels = $n->childNodes;
                foreach($rels as $rel)
                {
                    if ($rel->nodeName == "Relationship")
                    {
                        for($ix = 0;$ix < $rel->attributes->length;$ix++)
                        {
                            if ($rel->attributes->item($ix)->nodeName == "Id" && $rel->attributes->item($ix)->nodeValue == $id)
                            {
                                for($jx = 0;$jx < $rel->attributes->length;$jx++)
                                {
                                    if ($rel->attributes->item($jx)->nodeName == "Target")
                                        return $rel->attributes->item($jx)->nodeValue;
                                }
                            }
                        }
                    }
                }
            }
        }
        return '';
    }

    public function createRelationship($target,$type,$id)
    {
        if (!$this->_fileOpen)
        {
            $this->lasterror = WORDOC_FILE_NOT_OPEN;
            return null;
        }
        $this->lasterror = 0;
        $src = $this->_rels;
        $nodes = $src->childNodes;
        foreach ($nodes as $n)
        {
            if ($n->nodeName == 'Relationships')
            {
                $newnode = $src->createElement("Relationship");
                $newnode->setAttribute("Target",$target);
                $newnode->setAttribute("Type",$type);
                $newnode->setAttribute("Id",$id);
                $n->appendChild($newnode);
            }
        }
    }

    /*******************************************************************
        WORD IMAGES
    */

    public function replaceImage($filename,$data)
    {
        $h = fopen("{$this->_workingdir}/word/media/{$filename}","w");
        if ($h)
        {
            fwrite($h,$data,strlen($data));
            fclose($h);
        }
        else
            error_log("classWordDoc Unable to replace image {$filename} could not open file");
    }


    public function findImagePara($imageid,$startnode=null)
    {
        $idimage = strval($imageid);
        if ($startnode)
            $src = $startnode;
        else
            $src = $this->_body;
        $list = array();
        $this->findAllElementsOfType("w:drawing",$list);

        $numberfound = count($list);
        $this->deBug("findImagePara: Found {$numberfound} images");

        foreach ($list as $drawing)
        {
            $docptr = $this->findFirstDownstreamElement("wp:docPr",$drawing);
            if ($docptr)
            {
                $this->deBug("findImagePara: Found docPr attributes length {$docptr->attributes->length}");
                for($ix = 0;$ix < $docptr->attributes->length;$ix++)
                {

                    if ($docptr->attributes->item($ix)->nodeName == "id" && $docptr->attributes->item($ix)->nodeValue == $idimage)
                    {
                        //Found so return the para
                        $this->deBug("findImagePara: Found image id {$imageid}");
                        $p = $drawing->parentNode;
                        while ($p && $p->nodeName != WORDDOC_PARAGRAPH)
                        {
                            $p = $p->parentNode;
                        }
                        if ($p)
                        {
                            return $p;
                        }
                    }
                }
            }
        }
        return null;
    }


    public function findImagePara2($imageid,$startnode=null,$level=0)
    {
        $idimage = strval($imageid);
        //First find all images and image indexs
        if ($startnode)
            $src = $startnode;
        else
            $src = $this->_body;
        if ($src->hasChildNodes())
        {
            $nodes = $src->childNodes;
            foreach ($nodes as $n)
            {
                if ($n->nodeName == WORDDOC_PARAGRAPH)
                {
                    $drawing = $this->findFirstDownstreamElement("w:drawing",$n);
                    if ($drawing)
                    {
                        $docptr = $this->findFirstDownstreamElement("wp:docPr",$drawing);
                        if ($docptr)
                        {
                            for($ix = 0;$ix < $docptr->attributes->length;$ix++)
                            {

                                if ($docptr->attributes->item($ix)->nodeName == "id" && $docptr->attributes->item($ix)->nodeValue == $idimage)
                                {
                                    return $n;
                                }
                            }
                        }
                    }
                }

                if ($n->hasChildNodes())
                {
                    $cn = $n->childNodes;
                    foreach($cn as $nn)
                    {
                        $newn = $this->findImagePara($imageid,$nn,$level+1);
                        if ($newn)
                            return $newn;
                    }
                }

            }
        }
        return null;
    }

    public function findMaxImageNumber()
    {
        $max = 0;
        $list = array();
        $this->findAllElementsOfType("w:drawing",$list);
        foreach ($list as $drawing)
        {
            $docptr = $this->findFirstDownstreamElement("wp:docPr",$drawing);
            if ($docptr)
            {
                for($ix = 0;$ix < $docptr->attributes->length;$ix++)
                {

                    if ($docptr->attributes->item($ix)->nodeName == "id")
                    {
                        $in = intval($docptr->attributes->item($ix)->nodeValue);
                        if ($in > $max)
                            $max = $in;
                    }
                }
            }
        }
        return $max;
    }


    public function findMaxImageNumber2($startnode=null)
    {
        $max = 0;
        //First find all images and image indexs
        if ($startnode)
            $src = $startnode;
        else
            $src = $this->_body;
        if ($src->hasChildNodes())
        {
            $nodes = $src->childNodes;
            foreach ($nodes as $n)
            {
                if ($n->nodeName == WORDDOC_PARAGRAPH)
                {
                    $drawing = $this->findFirstDownstreamElement("w:drawing",$n);
                    if ($drawing)
                    {
                        $docptr = $this->findFirstDownstreamElement("wp:docPr",$drawing);
                        if ($docptr)
                        {
                            for($ix = 0;$ix < $docptr->attributes->length;$ix++)
                            {
                                if ($docptr->attributes->item($ix)->nodeName == "id")
                                {
                                    $in = intval($docptr->attributes->item($ix)->nodeValue);
                                    if ($in > $max)
                                        $max = $in;
                                }
                            }
                        }
                    }
                }

                if ($n->hasChildNodes())
                {
                    $cn = $n->childNodes;
                    foreach($cn as $nn)
                    {
                        $nmax = $this->findMaxImageNumber($nn);
                        if ($nmax > $max)
                            $max = $nmax;
                    }
                }
            }
        }
        return $max;
    }

    public function getImageParaFileName($imageid)
    {
        if ($imgp = $this->findImagePara($imageid) )
        {
            $drawing = $this->findFirstDownstreamElement("w:drawing",$imgp);
            if ($drawing)
            {
                $blip = $this->findFirstDownstreamElement("a:blip",$drawing);
                if ($blip)
                {
                    for($jx = 0;$jx < $blip->attributes->length;$jx++)
                    {
                        if ($blip->attributes->item($jx)->nodeName == "r:embed" )
                        {
                            //Get the tartget
                            return $this->getRelationshipTarget($blip->attributes->item($jx)->nodeValue);
                        }
                    }
                }
            }
        }
        return "";
    }

    public function duplicateImage($imageid,$startnode=null)
    {
        if ($imgp = $this->findImagePara($imageid,$startnode) )
        {
            $max = $this->findMaxImageNumber();
            $newimg = $imgp->cloneNode(true);
            $drawing = $this->findFirstDownstreamElement("w:drawing",$newimg);
            if ($drawing)
            {
                $docptr = $this->findFirstDownstreamElement("wp:docPr",$drawing);
                if ($docptr)
                {
                    for($ix = 0;$ix < $docptr->attributes->length;$ix++)
                    {
                        if ($docptr->attributes->item($ix)->nodeName == "id" )
                        {
                            $newnum = intval($max+1);
                            $docptr->attributes->item($ix)->nodeValue = strval($newnum);

                            //Now we need to find the blip
                            $blip = $this->findFirstDownstreamElement("a:blip",$drawing);
                            if (!$blip)
                                error_log("Could not find blip element");
                            for($jx = 0;$jx < $blip->attributes->length;$jx++)
                            {
                                if ($blip->attributes->item($jx)->nodeName == "r:embed" )
                                {
                                    //Get the tartget
                                    $filename1 = $this->getRelationshipTarget($blip->attributes->item($jx)->nodeValue);
                                    //Create a new random number
                                    $imgid = $this->createRandomInt();
                                    $refid = "rId" . strval($imgid);
                                    $blip->attributes->item($jx)->nodeValue = $refid;
                                    $this->createRelationship("media/image{$imgid}.png","http://schemas.openxmlformats.org/officeDocument/2006/relationships/image",$refid);
                                }
                            }
                            //Now we copy the actual image data
                            $filenameW = "image{$imgid}.png";
                            $data = file_get_contents("{$this->_workingdir}/word/{$filename1}");
                            $h = fopen("{$this->_workingdir}/word/media/{$filenameW}","w");
                            if($h)
                            {
                                fwrite($h,$data,strlen($data));
                                fclose($h);
                            }
                            else
                                error_log("classWordDoc Unable to create new image {$filename} could not open file");
                            return $newimg;
                        }
                    }
                }
            }
        }
        return null;
    }

    /*******************************************************************
        WORD TABLE FUNCTIONS
    */
    public function tableNewRow($tbl,$row)
    {
        $tbl->appendChild($row);
    }

    public function tableDeleteRow($tbl,$rowidx)
    {
        //Removes based o zero relative
        $cnt = 0;
        $l = $tbl->childNodes;
        if ($l->length > 0)
        {
            foreach($l as $k)
            {
                if($k->nodeName == "w:tr")
                {
                    if ($cnt == $rowidx)
                        return $tbl->removeChild($k);
                    $cnt++;
                }
            }
        }
        return null;
    }

    public function tableDeleteTable($tbl)
    {
        $p = $tbl->parentNode;
        if($p)
            return $p->removeChild($tbl);
        return null;
    }

    public function updateRowColumnText($row,$colidx,$text)
    {

        $tc = $this->findNthElement(WORDDOC_TABLE_CELL,$colidx,$row);
        if ($tc)
        {
            $e = $this->findFirstDownstreamElement("#text",$tc);
            if ($e)
            {
                $e->nodeValue = $text;
            }
        }
    }

    public function getTableColumnParagraph($row,$colidx)
    {
        $tc = $this->findNthElement(WORDDOC_TABLE_CELL,$colidx,$row);
        if ($tc)
        {
            return $this->findFirstDownstreamElement(WORDDOC_PARAGRAPH,$tc);
        }
    }

    public function replaceRowColumnParagraph($row,$colidx,$para)
    {

        $tc = $this->findNthElement(WORDDOC_TABLE_CELL,$colidx,$row);
        if ($tc)
        {
            $e = $this->findFirstDownstreamElement(WORDDOC_PARAGRAPH,$tc);
            if ($e)
            {
                $p = $e->parentNode;
                $this->deleteParagraph($e);
                $p->appendChild($para);
            }
        }
    }

    public function insertTableColumnParagraph($row,$colidx,$para)
    {
        $tc = $this->findNthElement(WORDDOC_TABLE_CELL,$colidx,$row);
        if ($tc)
        {
            $e = $this->findFirstDownstreamElement(WORDDOC_PARAGRAPH,$tc);
            if ($e)
            {
                $p = $e->parentNode;
                $p->appendChild($para);
            }
        }
    }

    public function updateTableRowCol($tbl,$rowidx,$colidx,$text)
    {
        if ($tbl)
        {
            $row = $this->findNthElement("w:tr",$rowidx,$tbl);
            if ($row)
                $this->updateRowColumnText($row,$colidx,$text);
        }
    }


    /*******************************************************************
        ADDITUIONAL PROPERTIES FUNCTIONS
    */
    public function createNewProperty($name,$value)
    {
        if ($this->_fileOpen)
        {
            $node = null;
            $doc = new DOMDocument();
            $propFileName = "{$this->_workingdir}/word/settings.xml";
            if (file_exists($propFileName))
            {
                $doc->load($propFileName);
                $node = $doc->firstChild;
                if (!$node)
                    echo "Node not found\n";
            }
            else
            {
                $node = $doc->createElement("w:settings");
                $doc->appendChild($node);
            }

            $newnode = $doc->createElement($name,$value);
            $node->appendChild($newnode);
            $doc->save($propFileName);
        }
    }
}
?>