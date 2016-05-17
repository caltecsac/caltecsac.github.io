<?php 

class DNSTipos {
    var $tipos_por_id;
    var $tipos_por_nombre;
    
    function AgregarTipo($id,$name) {
        $this->tipos_por_id[$id]=$name;
        $this->tipos_por_nombre[$name]=$id;
    }
    
    function DNSTipos() {
        $this->tipos_por_id=array();
        $this->tipos_por_nombre=array();
        
        $this->AgregarTipo(35,"NAPTR");
    }

    function RecuperarPorNombre($name) {
        if (isset($this->tipos_por_nombre[$name])) return $this->tipos_por_nombre[$name];
        return 0;
    }
        
    function RecuperarPorId($id) {
        if (isset($this->tipos_por_id[$id])) return $this->tipos_por_id[$id];
        return "";
    }
}

class DNSResultado {
    var $type;
    var $typeid;
    var $class;
    var $ttl;
    var $data;
    var $domain;
    var $string;
    var $extas=array();
}

class DNSRespuesta {
    var $count=0;
    var $results=array();
    
    function AgregarResultado($type,$typeid,$class,$ttl,$data,$domain="",$string="",$extras=array()) {
        $this->results[$this->count]=new DNSResultado();
        $this->results[$this->count]->type=$type;
        $this->results[$this->count]->typeid=$typeid;
        $this->results[$this->count]->class=$class;
        $this->results[$this->count]->ttl=$ttl;
        $this->results[$this->count]->data=$data;
        $this->results[$this->count]->domain=$domain;
        $this->results[$this->count]->string=$string;
        $this->results[$this->count]->extras=$extras;
        $this->count++;
        return ($this->count-1);
    }
}

class DNSConsulta {
    var $server="";
    var $port;
    var $timeout;
    
    var $types;
    
    var $rawbuffer="";
    var $rawheader="";
    var $rawresponse="";
    var $header;
    var $responsecounter=0;
    
    var $lastnameservers;
    var $lastadditional;
    
    var $error=false;
    var $lasterror="";
    
    function LeerRespuesta($count=1,$offset="") {
        if ($offset=="") {
            $return=substr($this->rawbuffer,$this->responsecounter,$count);
            $this->responsecounter+=$count;
        } else {
            $return=substr($this->rawbuffer,$offset,$count);
        }
        return $return;
    }
    
    function LeerEtiquetaDominios($offset,&$counter=0) {
        $labels=array();
        $startoffset=$offset;
        $return=false;
        while (!$return)
            {
            $label_len=ord($this->LeerRespuesta(1,$offset++));
            if ($label_len<=0) $return=true; // fin de datos
            else if ($label_len<64) {
                // datos sin comprimir
                $labels[]=$this->LeerRespuesta($label_len,$offset);
                $offset+=$label_len;
            } else {
                $nextitem=$this->LeerRespuesta(1,$offset++);
                $pointer_offset = ( ($label_len & 0x3f) << 8 ) + ord($nextitem);
                $pointer_labels=$this->LeerEtiquetaDominios($pointer_offset);
                foreach($pointer_labels as $ptr_label) {
                    $labels[]=$ptr_label;
                }
                $return=true;
                }
            }
        $counter=$offset-$startoffset;
        return $labels;
    }
    
    function LeerEtiquetaDominio() {
        $count=0;
        $labels=$this->LeerEtiquetaDominios($this->responsecounter,$count);
        $domain=implode(".",$labels);
        $this->responsecounter+=$count;
        return $domain;
    }
    
    function PonerError($text) {
        $this->error=true;
        $this->lasterror=$text;
    }
    
    function LimpiarError() {
        $this->error=false;
        $this->lasterror="";
    }
    
    function DNSConsulta($server,$port=53,$timeout=60) {
        $this->server=$server;
        $this->port=$port;
        $this->timeout=$timeout;
        
        $this->types=new DNSTipos();
    }
    
    
    function LeerRegistro() {
            
        $domain=$this->LeerEtiquetaDominio(); 
            
        $ans_header_bin=$this->LeerRespuesta(10); // 10 byte header
        $ans_header=unpack("ntype/nclass/Nttl/nlength",$ans_header_bin);
    
        $typeid=$this->types->RecuperarPorId($ans_header['type']);
        $extras=array();
        $data="";
        $string="";

        switch($typeid) {
            case "NAPTR":
                $data=$this->LeerRespuesta($ans_header['length']);
                $string=$domain." TEXT ".$data;
                break;
            default: 
                $data=$this->LeerRespuesta($ans_header['length']);
                $string=$domain." TEXT ".$data;
                break;
            }
    
        $return=array(
            "header" => $ans_header,
            "typeid" => $typeid,
            "data" => $data,
            "domain" => $domain,
            "string" => $string,
            "extras" => $extras );
        return $return;
    }

    function Query($question,$type="NAPTR") {

        $this->LimpiarError();
        $typeid=$this->types->RecuperarPorNombre($type);

        if ($typeid===false) {
            $this->PonerError("Invalid Query Type ".$type);
            return false;
        }
            
        $host="udp://".$this->server;
        
        if (!$socket=fsockopen($host,$this->port,$this->timeout)) {
            $this->PonerError("Failed to Open Socket");
            return false;
        }
            
        if (preg_match("/[a-z|A-Z]/",$question)==0) {
            $labeltmp=explode(".",$question);
            for ($i=count($labeltmp)-1; $i>=0; $i--) {
                $labels[]=$labeltmp[$i];
            }
            $labels[]="IN-ADDR";
            $labels[]="ARPA";
        } else { 
            $labels=explode(".",$question);
        }

        $question_binary="";

        for ($a=0; $a<count($labels); $a++) {
            $size=strlen($labels[$a]);
            $question_binary.=pack("C",$size); // primero byte tamaño
            $question_binary.=$labels[$a];     // luego el label
        }
        $question_binary.=pack("C",0);         // fin
        
        $id = rand(1,255)|(rand(0,255)<<8);      // generar ID al azar
        
        // Set standard codes and flags
        $flags  = 0x0100 & 0x0300;             // recursion & queryspecmask
        $opcode = 0x0000;                      // opcode
        
        // Build the header
        $header = "";
        $header.= pack("n",$id);
        $header.= pack("n",$opcode | $flags);
        $header.= pack("nnnn",1,0,0,0);
        $header.= $question_binary;
        $header.= pack("n",$typeid);
        $header.= pack("n",0x0001); // internet class
        $headersize = strlen($header);
        $headersizebin = pack("n",$headersize);
        
        if ( $headersize>=512) {
            $this->PonerError("Pregunta muy grande para UDP (".$headersize." bytes)");
            fclose($socket);
            return false;
        }
            
        if (!fwrite($socket,$header,$headersize)) {
            $this->PonerError("No pude escribir en el socket");
            fclose($socket);
            return false;
        }
        if (!$this->rawbuffer=fread($socket,4096))  {
            $this->PonerError("Fallo al escribir buffer de lectura");
            fclose($socket);
            return false;
        }                
        fclose($socket);
        
        $buffersize=strlen($this->rawbuffer);
        
        if ($buffersize<12) {
            $this->PonerError("Return Buffer too Small");
            return false;
        }
            
        $this->rawheader   = substr($this->rawbuffer,0,12); // first 12 bytes is the header
        $this->rawresponse = substr($this->rawbuffer,12);   // after that the response
        
        $this->responsecounter=12; // start parsing response counter from 12 - no longer using response so can do pointers
        
        $this->header = unpack("nid/nspec/nqdcount/nancount/nnscount/narcount",$this->rawheader);
        
        $answers     = $this->header['ancount'];
        
        $dns_answer=new DNSRespuesta();
        
        if ($this->header['qdcount']>0) {
            for ($a=0; $a<$this->header['qdcount']; $a++) {
                $c=1;
                while ($c!=0) {
                    $c=hexdec(bin2hex($this->LeerRespuesta(1)));
                }
                $extradata=$this->LeerRespuesta(4);
            }
        }

        for ($a=0; $a<$this->header['ancount']; $a++) {
            $record=$this->LeerRegistro();
            $dns_answer->AgregarResultado($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
                $record['data'],$record['domain'],$record['string'],$record['extras']);
        }
            
        $this->lastnameservers=new DNSRespuesta();
        for ($a=0; $a<$this->header['nscount']; $a++) {
            $record=$this->LeerRegistro();
            $this->lastnameservers->AgregarResultado($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
                $record['data'],$record['domain'],$record['string'],$record['extras']);
        }
            
        $this->lastadditional=new DNSRespuesta();
        for ($a=0; $a<$this->header['arcount']; $a++) {
            $record=$this->LeerRegistro();
            $this->lastadditional->AgregarResultado($record['header']['type'],$record['typeid'],$record['header']['class'],$record['header']['ttl'],
                $record['data'],$record['domain'],$record['string'],$record['extras']);
        }
        
        return $dns_answer; 
    }
    
}

?>
