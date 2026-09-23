(() => {
  const $ = s => document.querySelector(s), $$ = s => [...document.querySelectorAll(s)];
  const state = {home:null, heroIndex:0, heroTimer:null, user:null, csrf:window.MR?.csrf||'', loaded:{special:1,series:1,animation:1,movies:1}};
  const api = async (action, opts={}) => {
    const url = new URL('api.php', location.href); url.searchParams.set('action', action);
    Object.entries(opts.query||{}).forEach(([k,v])=>url.searchParams.set(k,v));
    const res = await fetch(url, {method:opts.method||'GET', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-Token':state.csrf}, body:opts.body?JSON.stringify(opts.body):undefined});
    let data; try{data=await res.json()}catch{throw new Error('پاسخ نامعتبر از سرور')}
    if(!res.ok || data.ok===false) throw new Error(data.message||'خطایی رخ داد');
    return data;
  };
  const toast = msg => {const el=$('#toast'); el.textContent=msg; el.classList.add('show'); clearTimeout(toast.t); toast.t=setTimeout(()=>el.classList.remove('show'),2800)};
  const esc = s => String(s??'').replace(/[&<>'"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m]));
  const card = (m, extra={}) => {
    const poster=m.poster||''; const title=esc(m.title||'بدون عنوان'); const year=esc(m.year||'—'); const type=m.type==='tv'?'سریال':'فیلم';
    return `<article class="card" data-type="${m.type||extra.type||'movie'}" data-id="${m.id}" data-title="${title}"><div class="poster-wrap"><img class="poster" loading="lazy" src="${poster}" alt="${title}" onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 600%22%3E%3Crect width=%22400%22 height=%22600%22 fill=%22%230d1925%22/%3E%3Ctext x=%22200%22 y=%22310%22 text-anchor=%22middle%22 fill=%22%23798a9f%22 font-size=%2224%22 font-family=%22Arial%22%3EMr Rashidi%3C/text%3E%3C/svg%3E'"/><span class="badge">${(m.rating||0)>=7.5?'4K':'Full HD'}</span><button class="fav-btn" data-fav aria-label="علاقه‌مندی">♡</button></div><div class="card-body"><div class="card-title">${title}</div><div class="card-en">${esc(m.original_title||'')}</div><div class="card-meta"><span>${year}</span><span>${type}</span>${m.rating?`<span>★ ${esc(m.rating)}</span>`:''}</div></div></article>`;
  };
  const renderRow=(id,items)=>{const el=$(id); if(!items?.length){el.innerHTML='<div class="empty">موردی پیدا نشد.</div>';return} el.innerHTML=items.map(card).join(''); bindCards(el)};
  const bindCards = root => {root.querySelectorAll('.card').forEach(c=>c.addEventListener('click',e=>{if(e.target.closest('[data-fav]'))return; openDetails(c.dataset.type,c.dataset.id)})); root.querySelectorAll('[data-fav]').forEach(b=>b.addEventListener('click',async e=>{e.stopPropagation(); try{const c=e.target.closest('.card'); const r=await api('favorite_toggle',{method:'POST',body:{type:c.dataset.type,id:c.dataset.id}}); b.textContent=r.favorite?'♥':'♡'; toast(r.favorite?'به علاقه‌مندی‌ها اضافه شد.':'از علاقه‌مندی‌ها حذف شد.'); loadFavorites()}catch(err){openAuthIfNeeded(err)}}))};
  const openModal=id=>$(id).classList.add('open'), closeModal=m=>m.classList.remove('open');
  $$('[data-close]').forEach(b=>b.addEventListener('click',()=>closeModal(b.closest('.modal'))));
  $$('.modal').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)closeModal(m)}));
  function openAuth(tab='login'){openModal('#authModal'); switchAuth(tab)}
  function openAuthIfNeeded(err){if(/وارد حساب/.test(err.message)){openAuth();}else toast(err.message)}
  function switchAuth(tab){$$('[data-auth-tab]').forEach(b=>b.classList.toggle('active',b.dataset.authTab===tab));$('#loginForm').classList.toggle('hidden',tab!=='login');$('#registerForm').classList.toggle('hidden',tab!=='register')}
  $$('[data-auth-tab]').forEach(b=>b.addEventListener('click',()=>switchAuth(b.dataset.authTab)));
  $('#accountBtn').addEventListener('click',()=>state.user?logout():openAuth()); $('#mobileAccount').addEventListener('click',()=>state.user?logout():openAuth());
  async function login(){try{const r=await api('login',{method:'POST',body:{email:$('#loginEmail').value,password:$('#loginPassword').value}});state.user=r.user;closeModal($('#authModal'));updateAccount();toast(r.message);loadFavorites();loadHistory()}catch(e){toast(e.message)}}
  async function register(){try{const r=await api('register',{method:'POST',body:{name:$('#registerName').value,email:$('#registerEmail').value,password:$('#registerPassword').value}});state.user=r.user;closeModal($('#authModal'));updateAccount();toast(r.message)}catch(e){toast(e.message)}}
  async function logout(){try{await api('logout',{method:'POST'});state.user=null;updateAccount();toast('از حساب خارج شدید.');$('#favoritesRow').innerHTML='<div class="empty">برای دیدن لیست علاقه‌مندی‌ها وارد حساب شوید.</div>'}catch(e){toast(e.message)}}
  $('#loginSubmit').addEventListener('click',login);$('#registerSubmit').addEventListener('click',register);
  function updateAccount(){if(state.user){$('#accountText').textContent=state.user.name;$('#avatar').textContent=state.user.name.slice(0,2).toUpperCase()}else{$('#accountText').textContent='ورود';$('#avatar').textContent='MR'}}
  async function loadHome(){try{const d=await api('home',{query:{page:1}});state.home=d;state.csrf=state.csrf||window.MR.csrf; renderHero();renderRow('#specialRow',d.special?.slice(0,10));renderRow('#seriesRow',d.series?.slice(0,10));renderRow('#animationRow',d.animation?.slice(0,10));renderRow('#moviesRow',d.movies?.slice(0,10));renderGenres(d.genres||[]);$('#appLoader').style.display='none'; startHero(); if(state.user){loadFavorites();loadHistory()}}catch(e){$('#appLoader').innerHTML='<div>بارگذاری اطلاعات ناموفق بود.</div><small style="color:#657589">'+esc(e.message)+'</small>'}}
  function renderHero(){const m=state.home.hero?.[state.heroIndex]||state.home.special?.[0]; if(!m)return; $('.hero-bg').style.backgroundImage=`linear-gradient(90deg,#040b14 5%,rgba(4,11,20,.7) 35%,rgba(4,11,20,.1) 75%),linear-gradient(0deg,#020811 0,transparent 28%),url("${m.backdrop||m.poster||''}")`;$('#heroTitle').textContent=m.title;$('#heroOverview').textContent=m.overview||'اطلاعات و جزئیات این اثر از TMDB دریافت می‌شود.';$('#heroMeta').innerHTML=`<span>${m.year||'—'}</span><span>${m.type==='tv'?'سریال':'فیلم'}</span>${m.rating?`<span>★ ${m.rating}</span>`:''}`;$('#heroInfo').onclick=()=>openDetails(m.type,m.id);$('#heroPlay').onclick=()=>openPlayer(m);$('#heroDots').innerHTML=(state.home.hero||[]).slice(0,6).map((_,i)=>`<i class="${i===state.heroIndex?'active':''}"></i>`).join('')}
  function startHero(){clearInterval(state.heroTimer);state.heroTimer=setInterval(()=>{if(!state.home?.hero?.length)return;state.heroIndex=(state.heroIndex+1)%state.home.hero.length;renderHero()},6000)}
  $('#heroNext').onclick=()=>{if(!state.home?.hero?.length)return;state.heroIndex=(state.heroIndex+1)%state.home.hero.length;renderHero();startHero()};$('#heroPrev').onclick=()=>{if(!state.home?.hero?.length)return;state.heroIndex=(state.heroIndex-1+state.home.hero.length)%state.home.hero.length;renderHero();startHero()};
  function renderGenres(genres){const preferred=[28,12,16,35,18,878,27,53,10749,80]; const list=[...preferred.map(id=>genres.find(g=>g.id===id)).filter(Boolean),...genres.filter(g=>!preferred.includes(g.id))].slice(0,10);$('#genreGrid').innerHTML=list.map(g=>`<button class="genre" data-genre="${g.id}"><span>${esc(g.name)}</span></button>`).join('');$$('[data-genre]').forEach(b=>b.addEventListener('click',()=>loadGenre(b.dataset.genre,b.textContent.trim())))}
  async function loadGenre(id,name){try{const d=await api('genre',{query:{genre:id,type:'movie',page:1}});$('#searchResults').classList.remove('hidden');$('#searchTitle').textContent=name;renderRow('#searchRow',d.items);location.hash='search';$('#searchResults').scrollIntoView({behavior:'smooth',block:'start'})}catch(e){toast(e.message)}}
  async function search(q){if(q.trim().length<2){$('#searchResults').classList.add('hidden');return}try{const d=await api('search',{query:{q:q.trim(),page:1}});$('#searchResults').classList.remove('hidden');$('#searchTitle').textContent='نتایج: '+q;renderRow('#searchRow',d.items);$('#searchResults').scrollIntoView({behavior:'smooth',block:'start'})}catch(e){toast(e.message)}}
  let searchTimer;$('#searchInput').addEventListener('input',e=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>search(e.target.value),450)});$('#clearSearch').addEventListener('click',()=>{$('#searchResults').classList.add('hidden');$('#searchInput').value='';location.hash='home'});
  async function openDetails(type,id){try{const d=await api('details',{query:{type,id}});const m=d.item;$('#detailHero').style.backgroundImage=`linear-gradient(0deg,#07111c,transparent 70%),url("${m.backdrop_url||m.poster_url||''}")`;$('#detailTitle').textContent=m.display_title;$('#detailMeta').innerHTML=`<span>${esc(m.year||'—')}</span><span>${type==='tv'?'سریال':'فیلم'}</span><span>★ ${esc(m.vote_average||'—')}</span>`;$('#detailOverview').textContent=m.overview||'توضیحی برای این اثر ثبت نشده است.';$('#detailPlay').onclick=()=>openPlayer({type,id,title:m.display_title});$('#detailFavorite').onclick=async()=>{try{const r=await api('favorite_toggle',{method:'POST',body:{type,id}});$('#detailFavorite').textContent=r.favorite?'♥ در علاقه‌مندی‌ها':'♡ علاقه‌مندی';loadFavorites();toast(r.favorite?'ذخیره شد.':'حذف شد.')}catch(e){openAuthIfNeeded(e)}};openModal('#detailsModal')}catch(e){toast(e.message)}}
  function openPlayer(m){closeModal($('#detailsModal'));$('#playerTitle').textContent=m.title||'پخش نمایشی';openModal('#playerModal'); if(state.user&&m.id)api('history_add',{method:'POST',body:{type:m.type||'movie',id:m.id,progress:0}}).catch(()=>{})}
  async function loadFavorites(){if(!state.user)return;try{const d=await api('favorites');if(!d.items.length){$('#favoritesRow').innerHTML='<div class="empty">هنوز فیلم یا سریالی ذخیره نکرده‌اید.</div>';return}renderRow('#favoritesRow',d.items.map(x=>({id:x.id,type:x.type,title:x.title,year:x.year,poster:x.poster,original_title:''})))}catch(e){toast(e.message)}}
  async function loadHistory(){if(!state.user)return;try{const d=await api('history');if(!d.items.length)return;renderRow('#historyRow',d.items.map(x=>({id:x.id,type:x.type,title:x.title,poster:x.poster,year:'',original_title:''})))}catch(e){}}
  $('#refreshFavorites').addEventListener('click',loadFavorites);
  $$('[data-open-plan]').forEach(b=>b.addEventListener('click',()=>openModal('#planModal')));$$('[data-demo-plan]').forEach(b=>b.addEventListener('click',()=>{closeModal($('#planModal'));toast(`پلن ${b.dataset.demoPlan} به صورت نمایشی انتخاب شد.`)}));
  $$('[data-load]').forEach(btn=>btn.addEventListener('click',async()=>{
    const key=btn.dataset.load; if(!['series','animation','movies','special'].includes(key)) return;
    const next=(state.loaded[key]||1)+1; btn.disabled=true; btn.textContent='در حال بارگذاری...';
    try{
      if(key==='special'){
        const d=await api('home',{query:{page:next}}); state.loaded[key]=next;
        const current=state.home?.special||[]; state.home.special=[...current,...(d.special||[])]; renderRow('#specialRow',state.home.special.slice(0,40));
      }else{
        const d=await api('home',{query:{page:next}}); state.loaded[key]=next; const map={series:'#seriesRow',animation:'#animationRow',movies:'#moviesRow'};
        const current=state.home?.[key]||[]; state.home[key]=[...current,...(d[key]||[])]; renderRow(map[key],state.home[key].slice(0,40));
      }
      toast('موارد بیشتری بارگذاری شد.');
    }catch(e){toast(e.message)}finally{btn.disabled=false;btn.textContent='مشاهده همه'}
  }));
  document.addEventListener('click',e=>{const a=e.target.closest('.side-link,.mobile-nav a,.topnav a');if(!a)return; $$('.side-link,.mobile-nav a,.topnav a').forEach(x=>x.classList.remove('active')); $$('.side-link,.mobile-nav a,.topnav a').filter(x=>x.getAttribute('href')===a.getAttribute('href')).forEach(x=>x.classList.add('active'));});
  api('bootstrap').then(d=>{state.csrf=d.csrf||state.csrf;state.user=d.user;updateAccount();loadHome()}).catch(()=>loadHome());
})();
