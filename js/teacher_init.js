function init_teacher(source, instance_id) {
    GWT_INSTANCE_ID = instance_id;
    script = document.createElement('script');
    script.setAttribute('type', 'text/javascript');
    script.setAttribute('src', source);
    script.onload = function() { teacher.onInjectionDone('teacher'); };
    document.getElementById('teacher_applet_toggle_' + instance_id).appendChild(script);
    document.getElementById('teacher_applet_toggle_' + instance_id).style.height = '80vh';
    document.dispatchEvent(new Event("DOMContentLoaded"));
    init_btn = document.getElementById('init_teacher_' + instance_id);
    init_btn && init_btn.parentNode.removeChild(init_btn);
}