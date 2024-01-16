
function showGrades(categoryID){
    let section_active= document.querySelector("[section=active]");
    let section_active_card= document.querySelector("[section=active-card]");
    let section_active_title= document.querySelector("[section=active-title]");
    
    if(section_active){
        section_active.style.display='none';
        section_active.setAttribute('section','disable');
    } 
    if(section_active_card){
        section_active_card.style.border='none';
        section_active_card.setAttribute('section','disable-card');
    } 
    if(section_active_title){
        section_active_title.style.color='#00003F';
        section_active_title.setAttribute('section','disable-card');
    } 
    let section = document.getElementById('category-'+categoryID);
    let section_card = document.querySelector('#card-'+categoryID+' a');
    let section_card_title = document.querySelector('#card-'+categoryID+' .category-level-title');
    section.style.display='block';
    section.setAttribute('section','active');
    section.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
    // section_card.style.border='4px solid #618E0E';
    section_card_title.style.color="#317839";
    section_card.setAttribute('section','active-card');
    section_card_title.setAttribute('section','active-title');
}
function showCourses(categoryID){
    let section_active= document.querySelector("[section=active]");
    let section_active_card= document.querySelector("[section=active-card]");
    let section_active_title= document.querySelector("[section=active-title]");
    
    if(section_active){
        section_active.style.display='none';
        section_active.setAttribute('section','disable');
    } 
    if(section_active_card){
        section_active_card.style.border='none';
        section_active_card.setAttribute('section','disable-card');
    } 
    if(section_active_title){
        section_active_title.style.color='#00003F';
        section_active_title.setAttribute('section','disable-card');
    } 
    let section = document.getElementById('category-course-'+categoryID);
    let section_card = document.querySelector('#card-course-'+categoryID+' a');
    let section_card_title = document.querySelector('#card-course-'+categoryID+' a .category-level-title');
    section.style.display='block';
    section.style.order='5';
    section.setAttribute('section','active');
    section.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
    // section_card.style.border='4px solid #618E0E';
    section_card_title.style.color="#317839";
    section_card.setAttribute('section','active-card');
    section_card_title.setAttribute('section','active-title');
}
function hiddeArrow(){
    document.getElementById('navigation-dashboard').style.display='none';

}

/**
 * Accesibilidad
 */
function showFocusGrades(id){
    let card = document.querySelector('#card-'+id+' a');
    card.addEventListener('keydown', function(event) {
        if (event.keyCode === 32) {
            event.preventDefault();
            // Realiza la acción deseada, como ejecutar un click en el elemento
            card.click();
        }
    });
}
function showFocusGuides(id){
    let card = document.querySelector('#card-course-'+id+' a');
    card.addEventListener('keydown', function(event) {
        if (event.keyCode === 32) {
            event.preventDefault();
            // Realiza la acción deseada, como ejecutar un click en el elemento
            card.click();
        }
    });
}