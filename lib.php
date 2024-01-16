<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing dashboard block instances.
 *
 * @copyright 2010 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_dashboard_toribio
 * @category  files
 * @param stdClass $course course object
 * @param stdClass $birecord_or_cm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
function block_dashboard_toribio_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_dashboard_toribio', 'content', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    // NOTE: it woudl be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are dispalyed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}

function block_dashboard_toribio_get_courses() {
    global $DB;
    $categories = core_course_category::get_all();
    $courses = array();
    foreach ($categories as $cat) {
    //         var_dump($cat->id);
    // var_dump("/hr");
    // var_dump($cat->name);
        $courses[] = $DB->get_records('course',array('category'=>$cat->id),'id','shortname');
    }

    //$totalcourses = $category->get_courses_count();
    // var_dump($categories);
    // var_dump("/hr");
    //var_dump($courses);
}


/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  $search
 * @param  $replace
 * @return void
 */
function block_dashboard_toribio_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'dashboard_toribio'));
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed
        $config = unserialize_object(base64_decode($instance->configdata));
        if (isset($config->text) and is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->update_record('block_instances', ['id' => $instance->id,
                    'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
        }
    }
    $instances->close();
}

function block_dashboard_toribio_main_content($cat = null) {
    
    global $DB,$CFG,$PAGE;
    // require_once($CFG->dirroot. '/course/lib.php');
    // require_once($CFG->libdir. '/coursecatlib.php');
    
    echo '<style>';
    include 'styles/main.css';
    echo '</style>';

    echo '<script>';
    include 'scripts/index.js';
    echo '</script>';
    
    $link_img_base='../blocks/dashboard_toribio/pix/';
    $link_course_base='/course/view.php?id=';
    $courseid=2;
    $url_actual = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];


    /**
     * Se verifica si hay alguna categoria seleccionada
     */

    $url_components = parse_url($url_actual);
 
    parse_str($url_components['query'], $params);
    $categories = core_course_category::get_all();
            
    // var_dump(__DIR__);
    $courses = array();
    $grades =  array();
    $child_categories= array();
    if($params['category']){
        // $ctn.=html_writer::start_tag('div', array('class' => 'row', 'id'=>'navigation-dashboard'));
        // $ctn.=html_writer::start_tag('a', ['href'=>$CFG->wwwroot.'/my','class'=>'d-flex align-items-strech col-12 navigation-container']);
        // $ctn .= html_writer::empty_tag('img', array('src' => $link_img_base.'back-arrow.svg','class' => '', 'alt' => ''));
        // $ctn .= html_writer::tag('p', get_string('regresar','block_dashboard_toribio'), array('class'=>'back-arrow-menu'));
        // $ctn .= html_writer::end_tag('a');
        // $ctn .= html_writer::end_tag('div');
        $coursecat = core_course_category::get($params['category']);
        $ctn .= html_writer::start_tag('nav',['aria-label'=>'Navigation bar']);
        $ctn .= html_writer::start_tag('ol',['class'=>'breadcrumb']);
        $ctn .= html_writer::start_tag('li',['class'=>'breadcrumb-item']);
        $ctn .= html_writer::start_tag('a',['href'=>$_SERVER['HTTP_REFERER']]);
        $ctn .= get_string('home', 'block_dashboard_toribio');
        $ctn .= html_writer::end_tag('a');
        $ctn .= html_writer::end_tag('li');
        $ctn .= html_writer::start_tag('li',['class'=>'breadcrumb-item']);
        $ctn .= $coursecat->name;
        $ctn .= html_writer::end_tag('li');
        $ctn .= html_writer::end_tag('ol');
        $ctn .= html_writer::end_tag('nav');

        /**
        * Back arrow navegacion
        */
        $ctn .= html_writer::start_tag('div', array('class' => 'col-12', 'id' => 'back-arrow', 'role' => 'figure'));
        $ctn .= html_writer::start_tag('a', array('href' => $_SERVER['HTTP_REFERER'], 'class' => 'd-flex navigation-container', 'role' => 'link', 'aria-label' => 'Regresar'));
        $ctn .= html_writer::empty_tag('img', array('src' => $link_img_base . 'back-arrow.svg', 'class' => '', 'alt' => 'Regresar', 'aria-hidden' => true));
        $ctn .= html_writer::tag('p', get_string('regresar', 'block_dashboard_toribio'), array('class' => 'back-arrow-menu', 'aria-labelledby' => 'back-arrow'));
        $ctn .= html_writer::end_tag('a');
        $ctn .= html_writer::end_tag('div');

        /* Titulo de la categoria
        * 
        */
        $ctn .= html_writer::start_tag('div', array('class' => 'row col-12', 'id' => 'guide-title', 'aria-labelledby' => 'guide-title'));
        $ctn .= html_writer::tag('h1', get_string('guidetitle', 'block_dashboard_toribio'), array('class' => 'dashboard-title col-12', 'id' => 'guide-title'));
        $ctn .= html_writer::end_tag('div');

        
    }
    $ctn.=html_writer::start_tag('div', array('class' => 'text-center'));
    $ctn.=html_writer::tag('h1', get_string($params['category']? 'reponotitle' : 'gradetitle', 'block_dashboard_toribio'),array('class' => 'dashboard-title', 'id'=> 'dashboard-title'));
    $ctn.=html_writer::tag('p', get_string($params['category']? 'reponotitle' : 'gradesubtitle', 'block_dashboard_toribio'),array('class' => 'dashboard-subtitle'));
    // $ctn.=html_writer::tag('h1', get_string($params['category']? 'reponotitle' : 'repotitle', 'block_dashboard_toribio'));
    $ctn.=html_writer::start_tag('div', array('class' => 'd-flex flex-wrap justify-content-center cards-container'));

    
    $ctn.=html_writer::start_tag('div', array('class' => 'row col-12  justify-content-center'));
    foreach ($categories as $cat) {
        
        /**
         * Si en la url hay una categoria este muestra a seccion de categorias nivel educativo
         */
        if($params['category']){

                if($cat->parent == $params['category']){
                    
                   $ctn.=html_writer::start_tag('div', array('class' => 'col-12 col-sm-6 col-md-4 col-lg-3 mb-4 mt-4'));
                   $ctn.=html_writer::start_tag('div', array('class' => '','id'=>'card-course-'.$cat->id));
                       if($cat->name == 'Transición'){
                           $ctn.=html_writer::start_tag('a', ['href'=>'?category='.$cat->id, 'role' => 'link', 'aria-label' => $cat->name]);
                           $ctn.=html_writer::empty_tag('img', array('src' => $link_img_base.$cat->name.'.svg','class' => 'card-category-level-image', 'alt' => ''));
                           $ctn .= html_writer::tag('p', $cat->name, array('class'=>'category-level-title'));
                           $ctn .= html_writer::end_tag('a');
                       }else{
                           $ctn.=html_writer::start_tag('a', ['href'=>'#','onclick'=>'showCourses('.$cat->id.')','onFocus'=>'showFocusGuides('.$cat->id.')', 'class' => 'card-category-level']);
                           $ctn.=html_writer::empty_tag('img', array('src' => $link_img_base.$cat->name.'.svg','class' => 'card-category-level-image', 'alt' => ''));
                           $ctn .= html_writer::tag('p', $cat->name, array('class'=>'category-level-title'));
                           $ctn .= html_writer::end_tag('a');
                           // $ctn.=html_writer::start_tag('a', ['onclick'=>'showCourses('.$cat->id.')']);
                           // $ctn.=html_writer::empty_tag('img', array('src' => $link_img_base.$cat->name.'.svg','class' => 'card-category-level-image', 'alt' => ''));
                           // $ctn .= html_writer::tag('p', $cat->name, array('class'=>'category-level-title'));
                           // $ctn .= html_writer::end_tag('a');
                       }
                       $ctn .= html_writer::end_tag('div');
                       $ctn .= html_writer::end_tag('div');

   
                       //Mostrar los cursos cargados
                       $cat = core_course_category::get($cat->id);
           
                       $courses_by_category =$cat->get_courses();
   
                       if(!empty($courses_by_category)){
                           /**
                            * Oculta los titulos y la navegacion hacia el dashboard y coloca la navegación a la
                            * pagina anterior
                            * 
                            */
                           echo "<script>
                               window.addEventListener('load', (event) => {
                                   let back_arrow = document.getElementById('navigation-dashboard');
                                   back_arrow.style.display='none';
                                   document.getElementById('dashboard-title').style.display='none';

                               });
                           </script>";
                           
                           $ctn.=html_writer::start_tag('div', ['id'=>'category-course-'.$cat->id, 'style'=>'display:none']);

                           $ctn .= html_writer::tag('p', 'Por favor, selecciona un curso para iniciar su recorrido de aprendizaje.', ['class'=>'grade-title col-12']);
                           $ctn.=html_writer::start_tag('div', array('class' => 'row col-12  mt-4 mb-4 card-guide-container'));
   
                       
                           $imgCounter=1;
                           foreach($courses_by_category as $guide){
                               $context = get_context_instance(CONTEXT_COURSE, $guide->id);
                               $enrrolluser = is_enrolled($context, $USER);
                               
                               if($enrrolluser || has_capability('moodle/site:config', $context)){
   
                               $ctn.=html_writer::start_tag('div', array('class' => 'card-guide p-0 text-left '));
                               
   
                               /**
                                * 
                                * Carga de a imagen del curso si no tiene se carga un div en blanco
                                * 
                                */
                               if(!empty($guide->get_course_overviewfiles())){
                                   foreach ($guide->get_course_overviewfiles() as $file) {
                                   
                                       if ($file->is_valid_image()) {
                                           $imagepath = '/' . $file->get_contextid() .
                                                   '/' . $file->get_component() .
                                                   '/' . $file->get_filearea() .
                                                   $file->get_filepath() .
                                                   $file->get_filename();
                                           $imageurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $imagepath,
                                                   false);
                                           $ctn .= html_writer::start_tag('div', ['class'=>'course-image']);
                                           $ctn.=html_writer::start_tag('a', ['href'=>$CFG->wwwroot.'/course/view.php?id='.$guide->id]);
                                           $ctn .=  html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'courseimage w-100 h-100'));
                                           $ctn .= html_writer::end_tag('a');
                                           $ctn .= html_writer::end_tag('div');
                   
                                           // Usa la primera imagen ue encuentra
                                           break;
                                       }
                                   }
                               }else{
                                   /**Imagen por defecto cuando no tienen imagen de curso */
                                //    $p = explode("/", $cat->path);
                                //    $parentName = core_course_category::get($p[1]);
                                //    // $imgPathName = strval($imgCounter).$parentName->name;
                                //    $imgPathName = strval($imgCounter).$cat->name;
                                   $ctn .= html_writer::start_tag('div', ['class'=>'course-not-image']);
                                   $ctn.=html_writer::start_tag('a', ['href'=>$CFG->wwwroot.'/course/view.php?id='.$guide->id]);
                                   if(file_exists(__DIR__.'/pix/guias/imagen-curso.svg')){
                                       $ctn .=  html_writer::empty_tag('img', array('src' => $link_img_base.'guias/imagen-curso.svg', 'class' => 'courseimage w-100 h-100'));
                                   }
                                   $ctn .= html_writer::end_tag('a');
                                   $ctn .= html_writer::end_tag('div');
                                   $imgCounter++;
                               }
   
                               $ctn .= html_writer::start_tag('div', ['class'=>'mt-3 mb-3 guide-info']);
                               $ctn.=html_writer::start_tag('a', ['href'=>$CFG->wwwroot.'/course/view.php?id='.$guide->id,'class'=>'guide-title']);
                               $ctn .= $guide->fullname;
                               $ctn .= html_writer::end_tag('a');
                               $ctn .= html_writer::tag('p', $cat->name, array('class'=>'guide-category'));
   
                               $progress_course = core_completion\progress::get_course_progress_percentage($guide);
                               $ctn.=html_writer::start_tag('div', ['class' => 'progress-text']);
                               if(!is_null($progress_course)){
                                   $ctn.=html_writer::start_tag('span');
                                   $progress_percent = bcdiv($progress_course, '1', 2);
                                   $ctn.= ($progress_percent < 1)? '0': $progress_percent;
                                   $ctn .= html_writer::end_tag('span');
                                   $ctn.='% complete';
                               } else{
                                   $ctn .= html_writer::start_tag('div', ['style'=>'height:24px']);
                                   $ctn .= html_writer::end_tag('div');
                               }
                               $ctn .= html_writer::end_tag('div');
                               $ctn .= html_writer::end_tag('div');
   
   
                               $ctn .= html_writer::end_tag('div');
                               }else{
                                   /**
                                   * no muestra nada si no esta mtriculado en el curso
                                   */
                               }
   
                           }
                           $ctn .= html_writer::end_tag('div');
                           $ctn .= html_writer::end_tag('div');
                   }
               
            }
            
        }
        else{
            /**
             * 
             * Muestras las categorias principales 
             * 
             */
            if($cat->parent == 0){
                if(file_exists(__DIR__.'/pix/'.$cat->name.'.svg')){
                    $ctn.=html_writer::start_tag('div', array('class' => 'col-12 col-sm-6 col-md-5 col-lg-3 mb-4 mt-4'));
                    $ctn.=html_writer::start_tag('div', array('id'=>'card-'.$cat->id, 'role' => 'figure'));
                    if($cat->name == 'Transición'){
                        $ctn.=html_writer::start_tag('a', ['href'=>'?category='.$cat->id,  'role' => 'link', 'aria-label' => $cat->name,'class' => 'card-category-level']);
                        $ctn .= html_writer::start_tag('figure', ['role' => 'figure']);
                        $ctn.=html_writer::empty_tag('img', array('src' => $link_img_base.$cat->name.'.svg', 'alt' => 'Imagen del grado '.$grade['name'],'class'=>'category-transicion card-category-level' ,'role' => 'img' ));
                        $ctn .= html_writer::end_tag('figure');
                        $ctn .= html_writer::tag('p', $cat->name, array('class'=>'category-level-title', 'aria-labelledby' => $cat->name));
                        $ctn .= html_writer::end_tag('a');
                    
                    }else{

                        $ctn.=html_writer::start_tag('a', ['href'=>'#','onclick'=>'showGrades('.$cat->id.')','onFocus'=>'showFocusGrades('.$cat->id.')', 'class' => 'card-category-level','role' => 'link', 'aria-label' => $cat->name]);
                        $ctn .= html_writer::start_tag('figure', ['role' => 'figure']);
                        $ctn.=html_writer::empty_tag('img', array('src' => $link_img_base.$cat->name.'.svg','class' => 'card-category-level', 'alt' => 'Imagen del grado '.$grade['name'],'role' => 'img' ));
                        $ctn .= html_writer::end_tag('figure');
                        $ctn .= html_writer::tag('p', $cat->name, array('class'=>'category-level-title', 'aria-labelledby' => $cat->name));
                        $ctn .= html_writer::end_tag('a');
                        array_push($child_categories,$cat->id);
                        $cate_child = core_course_category::get_all();
                        foreach ($cate_child as $cat_c) {
                            if($cat_c->parent == $cat->id){
                                array_push($grades,['name'=>$cat_c->name,'id'=>$cat_c->id,'parent_id'=>$cat_c->parent]);
                            }
                        }
                    }
                    $ctn .= html_writer::end_tag('div');
                    $ctn .= html_writer::end_tag('div');
 
                }
                
               
            }
        }
        
    }
    $ctn .= html_writer::end_tag('div');
    $ctn .= html_writer::end_tag('div');

    /**
     * 
     * categorias hijas los grados
     * 
     */
    $ctn.=html_writer::start_tag('div', array('class' => 'row'));
    foreach ($child_categories as $ch_c) {
    $ctn .= html_writer::start_tag('div', array('class' => 'col-12 mb-4 mt-4', 'id' => 'category-' . $ch_c, 'style' => 'display:none'));
    $ctn .= html_writer::start_tag('div', array('class' => 'row justify-content-center content-grades '));
    $ctn .= html_writer::tag('p', 'Selecciona el grado que te encuentras cursando o el que te gustaría visitar', ['class' => 'grade-title col-12', 'aria-labelledby' => 'Selecciona el grado que te gustaría visitar']);
        foreach ($grades as $grade) {
            if ($ch_c == $grade['parent_id']) {
                $ctn .= html_writer::start_tag('div', array('class' => 'col-12 col-sm-6 col-md-3 col-lg-2 mb-4 mt-4'));

                $ctn .= html_writer::start_tag('div', array('class' => ''));
                $ctn .= html_writer::start_tag('a', ['href' => '?category=' . $grade['id'], 'class' => 'card-category-level', 'role' => 'link', 'aria-label' => $grade['name']]);
                if (file_exists(__DIR__ . '/pix/' . $grade['name'] . '.svg')) {
                    $frame = html_writer::empty_tag('img', array('src' => $link_img_base . $grade['name'] . '.svg', 'class' => 'card-grade-image', 'alt' => 'Imagen del grado ' . $grade['name'],'role' => 'presentation'));
                    $ctn .= $frame;
                } else {
                    $ctn .= $grade['name'];
                }
                $ctn .= html_writer::end_tag('a');
                $ctn .= html_writer::end_tag('div');
                $ctn .= html_writer::end_tag('div');
            }
        }
        $ctn .= html_writer::end_tag('div');
        $ctn .= html_writer::end_tag('div');
    }

    $ctn .= html_writer::end_tag('div');
    $ctn .= html_writer::end_tag('div');
    
    return $ctn;
}

function block_dashboard_toribio_get_content($idnumber,$gap,$catidnum) {
    global $DB;$ctn='';$link_img_base='/theme/talentum/pix/';$link_course_base='/course/view.php?id=';$cat=$DB->get_record('course_categories',array('idnumber'=> $idnumber),'id');$courses = $DB->get_records('course',array('category'=>$cat->id));
    $ctn=html_writer::start_tag('div', array('class' => 'row mt-5 justify-content-center'));for ($i=0; $i < count($courses); $i++) {$frame = html_writer::empty_tag('img',
        array('src' => $link_img_base.'course/covers/'.$courses[$i+$gap]->idnumber.'.png','class' => 'icon-animation', 'alt' => ''));$ctn.=html_writer::start_tag('div', array('class' => 'col-2 mr-2'));
        $link = $link_course_base.$courses[$i+$gap]->id;
        $ctn.=html_writer::link($link,$frame);$ctn.= html_writer::end_tag('div');}
        $ctn.= html_writer::end_tag('div');
        $ctn.=html_writer::start_tag('div', array('class' => 'row justify-content-start'));
        $ctn.=html_writer::start_tag('div', array('class' => 'col-7 order-2 text-center align-self-center bg-text-box-left'));
            $ctn.=html_writer::tag('h5', get_string('catmessagestart', 'block_dashboard_toribio') .
            get_string('catmessage-'.$catidnum.'', 'block_dashboard_toribio') .
            get_string('catmessageend', 'block_dashboard_toribio'),
            array('class' => 'valle-font m-4'));
        $ctn.= html_writer::end_tag('div');$ctn.=html_writer::start_tag('div', array('class' => 'col-2 order-1'));
        $ctn.= html_writer::empty_tag('img', array('src' => $link_img_base.'cat-'.$catidnum.'.png','class' => 'img-fluid', 'alt' => ''));
        $ctn.= html_writer::end_tag('div');$ctn.= html_writer::end_tag('div');return $ctn;
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param  string $filearea The filearea.
 * @param  array  $args The path (the part after the filearea and before the filename).
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function block_dashboard_toribio_get_path_from_pluginfile(string $filearea, array $args) : array {
    // This block never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}
/**
 * Agrego la miga de pan 
 */
function block_dashboard_toribio_extend_navigation(global_navigation $navigation) {
    global $CFG,$PAGE;

    $previewnode = $PAGE->navigation->add(
        get_string('preview'),
        new moodle_url('/a/link/if/you/want/one.php'),
        navigation_node::TYPE_CONTAINER
    );
    $thingnode = $previewnode->add(
        get_string('thingname'),
        new moodle_url('/a/link/if/you/want/one.php')
    );
    $thingnode->make_active();

    // /**
    //  * Con este codigo agrego las breadcrumbs
    //  */
    // $previewnode = $navigation->add(
    //     'Inicio',
    //     new moodle_url($CFG->wwwroot.'/my'),
    //     navigation_node::TYPE_CONTAINER
    // );
    // $thingnode = $previewnode->add(
    //     get_string('title', 'local_my_courses'),
    //     new moodle_url($CFG->wwwroot.'/local/my_courses/pages/mycourses.php'),
    //     navigation_node::TYPE_ROOTNODE,
    //     'Courses',
    //     'courses',
    //     new pix_icon('i/course', '')
    // );
    // $thingnode->make_active();

}
