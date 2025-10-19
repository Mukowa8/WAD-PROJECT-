// ======= UniFlow app.js (full) =======
document.addEventListener('DOMContentLoaded', function(){
  initCourseList();
  buildTimetableGrid();
  attachCourseAdd();
  attachServerSave();
  loadDraftFromLocalStorage();
});

function initCourseList(){
  const list = document.getElementById('courseList');
  const saved = JSON.parse(localStorage.getItem('uniflow_courses')||'null');
  const defaultCourses = saved || [
    {id:'MCI512S', name:'Mathematics For computing', duration:1},
    {id:'PRG512s', name:'Programming', duration:2},
    {id:'DPG512s', name:'Database Programming', duration:1},
  ];
  localStorage.setItem('uniflow_courses', JSON.stringify(defaultCourses));
  list.innerHTML='';
  defaultCourses.forEach(c=>{
    const d = document.createElement('div'); d.className='course'; d.draggable=true;
    d.dataset.id=c.id; d.dataset.duration=c.duration; d.textContent = c.id + ' — ' + c.name + ' ('+c.duration+'h)';
    list.appendChild(d);
    d.addEventListener('dragstart', e=>{ e.dataTransfer.setData('text/plain', JSON.stringify({id:c.id,duration:c.duration,name:c.name})); d.classList.add('dragging'); });
    d.addEventListener('dragend', ()=>d.classList.remove('dragging'));
  });
}

function attachCourseAdd(){
  document.getElementById('addCourse').addEventListener('click', ()=>{
    const name = document.getElementById('newCourseName').value.trim();
    const dur = parseInt(document.getElementById('newCourseDuration').value) || 1;
    if (!name) return alert('Enter course code/name');
    const id = name.replace(/\s+/g,'').toUpperCase();
    const arr = JSON.parse(localStorage.getItem('uniflow_courses')||'[]');
    arr.push({id, name, duration:dur});
    localStorage.setItem('uniflow_courses', JSON.stringify(arr));
    initCourseList();
    document.getElementById('newCourseName').value=''; document.getElementById('newCourseDuration').value='';
  });
}

function buildTimetableGrid(){
  const tbody = document.getElementById('timetableBody');
  const times = ["08:00 - 09:00","09:00 - 10:00","10:00 - 11:00","11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00","15:00 - 16:00"];
  tbody.innerHTML='';
  for (let r=0;r<times.length;r++){
    const tr = document.createElement('tr');
    const tdTime = document.createElement('td'); tdTime.innerHTML='<strong>'+times[r]+'</strong>';
    tr.appendChild(tdTime);
    for (let d=0; d<5; d++){
      const td = document.createElement('td'); td.className='slot'; td.dataset.day=d; td.dataset.hour=r;
      td.addEventListener('dragover', e=>e.preventDefault());
      td.addEventListener('drop', onDrop);
      tr.appendChild(td);
    }
    tbody.appendChild(tr);
  }
}

function onDrop(e){
  e.preventDefault();
  const data = JSON.parse(e.dataTransfer.getData('text/plain'));
  const slot = e.currentTarget;
  const day = parseInt(slot.dataset.day); const hour = parseInt(slot.dataset.hour);
  const duration = parseInt(data.duration);
  if (checkConflict(day, hour, duration)){ alert('Time conflict detected'); return; }
  placeCourse(day, hour, data.id, data.name, duration);
  saveDraftToLocalStorage();
}

function placeCourse(day, hour, id, name, duration){
  for (let h=hour; h<hour+duration; h++){
    const sel = `.slot[data-day='${day}'][data-hour='${h}']`;
    const slot = document.querySelector(sel);
    if (!slot) continue;
    const pc = document.createElement('div'); pc.className='course-block';
    pc.dataset.courseId=id; pc.dataset.day=day; pc.dataset.hour=hour; pc.dataset.duration=duration;
    pc.textContent = id + ' — ' + name;
    pc.addEventListener('dblclick', ()=>{ pc.remove(); saveDraftToLocalStorage(); });
    slot.appendChild(pc);
  }
}

function checkConflict(day, startHour, duration){
  for (let h=startHour; h<startHour+duration; h++){
    const slot = document.querySelector(`.slot[data-day='${day}'][data-hour='${h}']`);
    if (!slot) return true;
    if (slot.querySelector('.course-block')) return true;
  }
  return false;
}

function saveDraftToLocalStorage(){
  const placed = [];
  document.querySelectorAll('.course-block').forEach(pc=>{
    placed.push({courseId: pc.dataset.courseId, day: parseInt(pc.dataset.day), hour: parseInt(pc.dataset.hour), duration: parseInt(pc.dataset.duration), title: pc.textContent});
  });
  localStorage.setItem('uniflow_draft', JSON.stringify(placed));
  console.log('Draft saved locally');
}

function loadDraftFromLocalStorage(){
  const data = JSON.parse(localStorage.getItem('uniflow_draft')||'null');
  if (!data) return;
  document.querySelectorAll('.course-block').forEach(n=>n.remove());
  data.forEach(item=> placeCourse(item.day, item.hour, item.courseId, item.title.split(' — ').slice(1).join(' — '), item.duration) );
}

function clearDraft(){ if (!confirm('Clear draft?')) return; localStorage.removeItem('uniflow_draft'); document.querySelectorAll('.course-block').forEach(n=>n.remove()); }

function attachServerSave(){
  document.getElementById('saveServerBtn').addEventListener('click', async ()=>{
    const title = prompt('Title for schedule','My Schedule');
    if (!title) return;
    const placed = JSON.parse(localStorage.getItem('uniflow_draft')||'[]');
    const res = await fetch('api/save_schedule.php',{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ title, data: placed }) });
    const json = await res.json();
    if (json.success) alert('Saved to server'); else alert(json.error||'Save failed');
  });
}

async function loadSchedulesFromServer(){
  const res = await fetch('api/get_schedules.php');
  const json = await res.json();
  if (!json.success) return [];
  return json.schedules;
}
