<?php

function unify_os4000($file,$name){

    $cdrs = Storage::disk('local')->get($file);
    $cdrs = preg_split('/(\r|\n)/', $cdrs);
        
    //Usa o ano do bilhete de referencia para as ligações
    list($date, $time) = explode('_', basename($file));
    list($D, $M, $Y) = explode('-', $date);
       
    foreach($cdrs as $cdr):
        //limpa as variaveis
        $pbx='' ; $calldate=''; $extensions_id=''; $trunks_id=''; $did=''; $direction=''; $dialnumber=''; $callnumber='';
        $ring=''; $billsec=''; $accountcodes_id=''; $projectcodes_id=''; $disposition=''; $status_id='';  $uniqueid=''; 

        //se a linha for branca continua
        if(!substr($cdr,0,2)):
            continue;
        endif;
            
        $pbx = $name;                                                                           // pbx do bilhete
        $his               = trim(substr($cdr,0,8));                                            // hora do bilhete
        list($d,$m,$y)     = explode('/',trim(substr($cdr,9,8)));                               // data do bilhete
        $calldate          = "$Y-$m-$d $his";                                                   // data e hora da chamada convertida
        $extensions_id     = trim(substr($cdr,35,4));                                           // Ramal que originou a ligação
        $ring              = '0';                                                               // toques antes do atendimento  
        $billsec           = trim(substr($cdr,19,8));                                           // duração da chamada no bilhete
        $billsec           = strtotime($billsec?$billsec:'00:00:00') - strtotime('00:00:00');   // duração da chamada convertida
        $accountcodes_id   = trim(substr($cdr,105,10));                                         // senha de ligação no bilhete
        //$projectcodes_id = trim(substr($cdr,105,10));                                         // codigo de projeto no bilhete
        $disposition       = trim(substr($cdr,73,2));                                           // flags da chamada no bilhete                                           
        $uniqueid          = trim(substr($cdr,144,6));                                          // unique id da chamada no bilhete
        $status_id         = 0;                                                                 // status 0 nao tarifada                        
        
        //checa o tipo da chamada
        switch(trim(substr($cdr,108,2))):
           
            //chamadas internas     
            case 'IN':
                $dialnumber = trim(substr($cdr,80,25));                                         // numero discado no bilhete
                $direction  = 'IN';                                                             // insere a flag IN para chamadas internas
                break;
           
            //chamadas de entrada
            case 'IC':
                $trunks_id  = trim(substr($cdr,39,6));                                          // tronco usado para a chamadas no bilhete
                $dialnumber = trim(substr($cdr,80,25));                                         // numero discado no bilhete
                $callnumber = dialIc($dialnumber, $trunks_id, $pbx);                            // numero convertido no padrão e.164
                $direction  = 'IC';                                                             // insere a flag IC para chamadas de entrada
                break;
            
            //chamada de saida
            case 'OG':
                $trunks_id  = trim(substr($cdr,39,6));                                          // tronco usado para chamadas no bilhete
                $dialnumber = trim(substr($cdr,80,25));                                         // numero discado no bilhete
                $callnumber = dialOc($dialnumber, $trunks_id, $pbx);                            // numero convertido no padrão e.164
                $direction  = 'OC';                                                             // insere a flag OC para chamadas de saida
                break;
                            
            default :
        endswitch;
        
        //se a data do bilhete for antes da data de coleta coloca o ano da coleta se nao um ano antes
        $time1 = str_replace('-', ':', substr($time,0,8));
        $now = strtotime($date.' '.$time1);
        $calldate = strtotime(date($calldate));
        if($now < $calldate):
            $calldate =  date('Y-m-d H:i:s', strtotime('-1 year', $calldate));
        else:
            $calldate =  date('Y-m-d H:i:s', strtotime('0 days',$calldate));
        endif;
        
        // insere no banco apenas ligações de entrada interna ou saida indentificadas no bilhete
        if($direction != ''):
            $call = App\Models\Call::updateOrCreate(
                    [
                        'pbx' => $pbx, 
                        'calldate' => $calldate, 
                        'extensions_id' => $extensions_id, 
                        'trunks_id' => $trunks_id, 
                        'did' => $did, 
                        'direction' => $direction, 
                        'dialnumber' => $dialnumber, 
                        'ring' => $ring, 
                        'billsec' => $billsec, 
                        'accountcodes_id' => $accountcodes_id, 
                        'projectcodes_id' => $projectcodes_id, 
                        'uniqueid' => $uniqueid, 
                        'disposition' => $disposition, 
                    ],
                    [
                        'pbx' => $pbx, 
                        'calldate' => $calldate, 
                        'extensions_id' => $extensions_id, 
                        'trunks_id' => $trunks_id, 
                        'did' => $did, 
                        'direction' => $direction, 
                        'dialnumber' => $dialnumber, 
                        'callnumber' => $callnumber, 
                        'ring' => $ring, 
                        'billsec' => $billsec, 
                        'accountcodes_id' => $accountcodes_id, 
                        'projectcodes_id' => $projectcodes_id, 
                        'disposition' => $disposition, 
                        'uniqueid' => $uniqueid, 
                        'status_id' => $status_id, 
                    ]
            );
        
        endif;
        //dd($call);
    endforeach;
    
    return true;
}
    
