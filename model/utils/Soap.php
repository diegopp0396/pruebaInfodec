<?php

class Soap
{
    function callSoapCurlWS($wsdl, $xmlRequest, $namespaces, $action, $headers = "")
    {
        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: " . $action,
            "Content-length: " . strlen($xmlRequest)
        );

        if( !empty($headers) ){
            foreach($headers as $key => $val){
                array_push($header, $key.": ".$val);
            }
        }

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $wsdl );
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST,           true );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $xmlRequest);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);

        $response = curl_exec($soap_do);
        $httpStatus = curl_getinfo($soap_do, CURLINFO_HTTP_CODE);
        $err     = curl_errno($soap_do);
        $errmsg  = curl_error($soap_do);

        if( ($response === false && $httpStatus === 0) ||  $httpStatus === 400 ) {
            curl_close($soap_do);
            $retorno = array('Error' => $err, 'httpStatus' => $httpStatus, 'Message' => $errmsg);
        } else {
            // converting
            $clean_xml = $response;

            if( !empty($namespaces ) ){
                foreach($namespaces as $name => $val ){
                    $clean_xml = str_replace($val, '', $clean_xml);
                }
            }

            $retorno = simplexml_load_string($clean_xml);
            curl_close($soap_do);

        }

        return $retorno;
    }
}