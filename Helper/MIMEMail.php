<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

class MIMEMail{
 var $to;
 var $boundary;
 var $smtp_headers;
 var $filename_real;
 var $body_plain;
 var $body_html;
 var $atcmnt; var $atcmnt_type;

 function MIMEMail($to, $from, $subject, $priority=3) {
  $this->to = $to;
  $this->from = $from; 
  $this->subject = $subject;
  $this->boundary = "----=_NextPart_".time()."_".md5(time())."_";
 }

 function mailbody($plain, $html = ''){
  $this->body_plain = $plain;
  $this->body_html = $html;
 }

 function  attachfile($fname, $content_type) {
  if($f = @fopen($fname,"r")){
   $this->atcmnt[$fname] = fread($f, filesize($fname));
   $this->atcmnt_type[$fname] = $content_type;
   fclose($f);
  } 
 }

 function clear($fname){
  unset($this->atcmnt[$fname]);
  unset($this->atcmnt_type[$fname]);
 }

 function  makeheader() {
   $out ="From: ".$this->from."\n";
   $out.="Reply-To: ".$this->from."\n";
   $out.="MIME-Version: 1.0\nContent-Type: multipart/mixed;\n\t boundary=\"".$this->boundary."\"\n";
   return $out;
 }

 function  makebody() {
   $boundary2= "----=_NextAttachedPart_".time()."_".md5(time()+101)."_";
   $out="";
   if($this->body_html) {
     $out="\nThis is a multi-part message in MIME format.\n\n";
     $out.="--".$this->boundary."\nContent-Type: multipart/alternative;\n\tboundary=\"$boundary2\"\n";
     $out.="\n".
    "--$boundary2\nContent-Type: text/plain\n".
#    "Content-Disposition: inline\n".
    "Content-Transfer-Encoding: quoted-printable\n\n".
    $this->body_plain.
    "\n\n--$boundary2\n".
    "Content-Type: text/html\n".
#    "Content-Disposition: attachment;\n\tfilename=\"message.html\"\n".
           "Conent-Transfer-Encoding: quoted-printable\n".
    "\n$this->body_html\n\n".
    "--$boundary2--\n";
   } else {
     $out="\n\n".$this->body_plain."\n\n";
     $out.="--".$this->boundary."\n".
 "Content-Type: text/plain\n".
 "Content-Transfer-Encoding: quoted-printable\n\n".
 $this->body_plain.
 "\n\n--".$this->boundary.
 "\n";
   }
   if( is_array( $this->atcmnt_type ) ) {
     reset( $this->atcmnt_type);
     while( list($name, $content_type) = each($this->atcmnt_type) ) {
       $out.="\n--".$this->boundary."\nContent-Type: $content_type\nContent-Transfer-Encoding: base64\nContent-Disposition: attachment; filename=\"$name\"\n\n".
         chunk_split(base64_encode($this->atcmnt[$name]))."\n";
     }   
   }
   $out.="\n--".$this->boundary."--\n";
   return $out;
 }

 function send(){
  mail($this->to, $this->subject, $this->makebody(), $this->makeheader());
 }

}
?>