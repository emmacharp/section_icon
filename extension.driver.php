<?php
class extension_section_icon extends Extension
{
    
    const DB_TABLE = 'tbl_sections';
    
    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/backend/',
                'delegate' => 'InitaliseAdminPageHead',
                'callback' => 'dInitaliseAdminPageHead'
            ),
            array(
                'page' => '/blueprints/sections/',
                'delegate' => 'AddSectionElements',
                'callback' => 'dAddSectionElements'
                )
            );
        }
        
        public function dInitaliseAdminPageHead () 
        {
            $sections = SectionManager::select()->execute()->rows();
            $json = '{';
                $i = 0;
                
                foreach ($sections as $key => $section) {
                    $icon = $section->get('icon');
                    $icon = str_replace('"', '\"', $icon);
                    $icon = str_replace('> <', '><', $icon);
                    $icon = preg_replace('/\r|\t/', '', $icon);
                    $json .=  '"' . $key . '": "' . $icon . '",';
                    $json .=  '"' . $section->get('handle') . '":"' . $icon . '"';
                    
                    if (count($sections) - 1 !== $i) {
                        $json .= ',';
                    }
                    
                    $i++;
                }
                
                $json .= '}';
                
                $tag = new XMLElement('script', $json, array('type'=>'application/json', 'id' => 'section_icon'));
                Administration::instance()->Page->addElementToHead($tag);
                Administration::instance()->Page->addScriptToHead(URL . '/extensions/section_icon/assets/section_icon.js');
            }
            
            private function getChildrenWithClass($rootElement, $tagName, $className) {
                if (! ($rootElement) instanceof XMLElement) {
                    return null; // not and XMLElement
                }
                
                // contains the right css class and the right node name
                if (strpos($rootElement->getAttribute('class'), $className) > -1 && $rootElement->getName() == $tagName) {
                    return $rootElement;
                }

                // recursive search in child elements
                foreach ($rootElement->getChildren() as $child) {

                    $res = $this->getChildrenWithClass($child, $tagName, $className);
                    
                    if ($res != null) {
                        return $res;
                    }
                }
                
                return null;
            }
            
            public function dAddSectionElements ($context)
            {
                $fieldset = new XMLElement('fieldset', null, array('class' => 'settings'));
                $legend = new XMLElement('legend', __('Section SVG Icon'));
                $label = Widget::Label(__('Icon'));
                $label->appendChild(Widget::Input("meta[icon]", $context['meta']['icon']));
                $label->appendChild(new XMLElement('p', __('Associate a section with an svg icon. Simply paste the svg here.'), array('class' => 'help')));
                $fieldset->appendChild($legend);
                $fieldset->appendChild($label);
                $this->getChildrenWithClass($context['form'], 'div', 'inner')->appendChild($fieldset);
            }
            
            public function install()
            {
                try {
                    Symphony::Database()
                    ->alter(self::DB_TABLE)
                    ->add([
                        'icon' => [
                            'type' => 'text',
                            'null' => true,
                        ],
                        ])
                        ->after('hidden')
                        ->execute()
                        ->success();
                    } catch (DatabaseException $dbe) {
                        if ($this->_tried_installation === false) {
                            $this->_tried_installation = true;
                            
                            $this->uninstall();
                            
                            return $this->install();
                        }
                    }
                    
                    $this->_tried_installation = true;
                    
                    return true;
                }
                
                public function uninstall()
                {
                    try {
                        Symphony::Database()
                        ->alter(self::DB_TABLE)
                        ->drop('icon')
                        ->execute()
                        ->success();
                    } catch (DatabaseException $dbe) {
                    }
                    
                    return true;
                }
                
            }
