<?php 
/**
 * @package     mod_droideforms.Plugin
 * @subpackage  droide-fromscronjob
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author 		André Luiz Pereira <[<and4563@gmail.com>]>
 */

defined('_JEXEC') or die ();



class plgSystemFormscronjob extends JPlugin{

    private $forms_sended = [];
    private $dados_disparo = [];

    public function __construct(&$subject, $config)
    {

        parent::__construct($subject, $config);

    }



   /**
     * Organiza os elementos registrados no admin de forma amigavel para tratamento
    * @param  array $parans_bruto - array com os atributos vindos do repeteable
    * @return array array bruto tratado
    * @author André Luiz Pereira <andre@next4.com.br>
    */
    private function organizeArray($parans_bruto)
    {
        
        $total = count($parans_bruto['form_id']);

        $resultado = [];

        for ($i=0; $i <$total; $i++)
        { 
            $resultado[] = [
                'form_id'=>$parans_bruto['form_id'][$i],
                'emails'=>$parans_bruto['emails'][$i],
                'assunto'=>$parans_bruto['assunto'][$i],
                'periodo_envio'=>$parans_bruto['periodo_envio'][$i],
                'layout'=>$parans_bruto['layout'][$i],
            ];
        }

        return $resultado;
    }


    public function onAfterRoute()
    {

        $key = $this->params->get('key','dsfjdwojnsndfpsjdf_4654545446151');
        $jinput = JFactory::getApplication()->input;
       $get = $jinput->get('cron_droideforms', '', 'STRING');

        if($get == $key)
        {
            $params_bruto = json_decode($this->params->get('addtable',0),true);

            $parans = $this->organizeArray($params_bruto);

            // date('Y-m-d H:i:s');


            if($this->checkDisparo($parans))
            {
                
                $this->disparar();
                
            }

            exit;
            
            
        }

        
    }

    private function checkDisparo($parans)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $ids = [];

        foreach($parans as $param =>$val)
        {
            if($this->checkPeriod($val))
            {
                $ids[] = $db->quote($val['form_id']); 

                $this->dados_disparo[$val['form_id']] = [
                    'emails'=>$val['emails'],
                    'layout'=>$val['layout'],
                    'assunto'=>$val['assunto'],
                ];
            }
            
        }


        if($ids)
        {

            $id_implode = implode(',',$ids);

            $query->select('*')
            ->from($db->quoteName('#__droide_forms_register'))
            ->where("form_id in(".$id_implode.") and NOT EXISTS (select id from ".$db->quoteName('#__droide_forms_cronjob')." as cron where ".$db->quoteName('#__droide_forms_register').".id = cron.id_register)");

            $db->setQuery($query);

            $this->forms_sended = $db->loadAssocList();

            if($this->forms_sended)
            {
                return true;
            }

        }

        

        return false;

    }


    public function checkPeriod($val)
    {
        $dia_da_semana = date('w');

            if($val['periodo_envio'] == 'd' || (string)$val['periodo_envio'] == (string)$dia_da_semana)
            {
                return true;
            }

        return false;

    }

    private function disparar()
    {

        // $this->dados_disparo[$val['form_id']] = [
        //             'emails'=>$val['emails'],
        //             'layout'=>$val['layout'],
        //             'assunto'=>$val['assunto'],
        //         ];

        $config = JFactory::getConfig();


            $sender = array(
               $config->get( 'mailfrom' ),
               $config->get( 'fromname' ),
            );

                foreach($this->dados_disparo as $id_form =>$campos)
                {

                    $emailTO = $campos['emails'];

                    if(strpos($emailTO, ';') !== false)
                    {
                        $emailTO = explode(';',$emailTO);
                    }

                    $getLayout = $this->getLayout($campos['layout']);

                    

                     $mail = JFactory::getMailer();
                     $mail->isHTML(true);
                     $mail->Encoding = 'base64';
                     $mail->addRecipient($emailTO);
                     $mail->setSender($sender);

                     $mail->setSubject($campos['assunto']);
                     $mail->setBody($getLayout);

                     $envio = $mail->Send();

                     if($envio == 1)
                     {

                         foreach ($this->forms_sended as $k => $sender) {

                                    if($sender['form_id'] == $id_form)
                                    {
                                            $db = JFactory::getDbo();
                                            $query = $db->getQuery(true);
                                            $query
                                              ->insert($db->quoteName('#__droide_forms_cronjob'))
                                              ->columns('form_id,id_register')
                                              ->values($db->quote($id_form).','.$db->quote($sender['id']))
                                              ;
                                            $db->setQuery($query);
                                            $return = $db->execute();    
                                    }   

                            }

                     }

                     
                       
                            


                }

    }




    private function getLayout($layout)
    {

        $layout = new JLayoutFile($layout, JPATH_ROOT .'/plugins/system/formscronjob/layout');

        $html = $layout->render(['forms'=>$this->forms_sended]);

        return $html;
    }




}