<?php
require_once __DIR__ . '/tmdb.php';

function input_json(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}
function clean_email(string $email): string { return strtolower(trim($email)); }
function valid_password(string $p): bool { return strlen($p) >= 8 && strlen($p) <= 128; }

$action = $_GET['action'] ?? '';
try {
    switch ($action) {
        case 'bootstrap':
            $user = current_user();
            json_response(['ok'=>true,'csrf'=>csrf_token(),'user'=>$user,'tmdb_configured'=>tmdb_configured()]);

        case 'register':
            require_csrf();
            $d = input_json();
            $name = trim((string)($d['name'] ?? ''));
            $email = clean_email((string)($d['email'] ?? ''));
            $password = (string)($d['password'] ?? '');
            if (mb_strlen($name) < 2 || mb_strlen($name) > 80) json_response(['ok'=>false,'message'=>'نام باید بین ۲ تا ۸۰ کاراکتر باشد.'],422);
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)) json_response(['ok'=>false,'message'=>'ایمیل معتبر نیست.'],422);
            if (!valid_password($password)) json_response(['ok'=>false,'message'=>'رمز عبور باید حداقل ۸ کاراکتر باشد.'],422);
            $stmt=db()->prepare('SELECT id FROM users WHERE email=?'); $stmt->execute([$email]);
            if ($stmt->fetch()) json_response(['ok'=>false,'message'=>'این ایمیل قبلاً ثبت شده است.'],409);
            $stmt=db()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            session_regenerate_id(true); $_SESSION['user_id']=(int)db()->lastInsertId();
            json_response(['ok'=>true,'user'=>current_user(),'message'=>'حساب با موفقیت ساخته شد.']);

        case 'login':
            require_csrf();
            $d=input_json(); $email=clean_email((string)($d['email']??'')); $password=(string)($d['password']??'');
            if (!filter_var($email,FILTER_VALIDATE_EMAIL) || $password==='') json_response(['ok'=>false,'message'=>'ایمیل یا رمز عبور صحیح نیست.'],422);
            $stmt=db()->prepare('SELECT * FROM users WHERE email=?'); $stmt->execute([$email]); $u=$stmt->fetch();
            if (!$u || !password_verify($password,$u['password_hash'])) json_response(['ok'=>false,'message'=>'ایمیل یا رمز عبور صحیح نیست.'],401);
            if (password_needs_rehash($u['password_hash'],PASSWORD_DEFAULT)) { $s=db()->prepare('UPDATE users SET password_hash=? WHERE id=?'); $s->execute([password_hash($password,PASSWORD_DEFAULT),$u['id']]); }
            session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id'];
            json_response(['ok'=>true,'user'=>current_user(),'message'=>'خوش آمدید.']);

        case 'logout':
            require_csrf();
            $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',(bool)$p['secure'],(bool)$p['httponly']); }
            session_destroy(); json_response(['ok'=>true]);

        case 'home':
            $page=max(1,min(20,(int)($_GET['page']??1)));
            $trending=tmdb_cached('trending_all_day',fn()=>tmdb_request('trending/all/day',['language'=>'fa-IR']),300);
            $tv=tmdb_cached('popular_tv_'.$page,function() use($page){return tmdb_request('tv/popular',['page'=>$page]);},900);
            $movies=tmdb_cached('now_playing_'.$page,function() use($page){return tmdb_request('movie/now_playing',['page'=>$page,'region'=>'US']);},600);
            $animation=tmdb_cached('animation_movie_'.$page,function() use($page){return tmdb_request('discover/movie',['page'=>$page,'with_genres'=>16,'sort_by'=>'popularity.desc']);},900);
            $genreData=tmdb_cached('genres_movie',fn()=>tmdb_request('genre/movie/list',[]),86400);
            $genres=[];
            foreach (($genreData['genres']??[]) as $g) {
                $genres[]=['id'=>(int)$g['id'],'name'=>$g['name']];
            }
            $norm=function($arr){$out=[]; foreach (($arr['results']??[]) as $i) { $type=isset($i['first_air_date'])?'tv':'movie'; $out[]=normalize_media($i,$type); } return $out;};
            $hero=[]; foreach (($trending['results']??[]) as $i) { if (!empty($i['backdrop_path'])) $hero[]=normalize_media($i,isset($i['first_air_date'])?'tv':'movie'); if(count($hero)>=6)break; }
            json_response(['ok'=>true,'hero'=>$hero,'special'=>$norm($trending),'series'=>$norm($tv),'animation'=>$norm($animation),'movies'=>$norm($movies),'genres'=>$genres,'page'=>$page,'pages'=>max((int)($tv['total_pages']??1),(int)($movies['total_pages']??1))]);

        case 'genre':
            $type=($_GET['type']??'movie')==='tv'?'tv':'movie'; $genre=(int)($_GET['genre']??0); $page=max(1,min(20,(int)($_GET['page']??1)));
            if(!$genre) json_response(['ok'=>false,'message'=>'ژانر نامعتبر است.'],422);
            $d=tmdb_cached("discover_{$type}_{$genre}_{$page}",function() use($type,$genre,$page){return tmdb_request('discover/'.$type,['page'=>$page,'with_genres'=>$genre,'sort_by'=>'popularity.desc']);},900);
            $items=[]; foreach(($d['results']??[]) as $i)$items[]=normalize_media($i,$type);
            json_response(['ok'=>true,'items'=>$items,'page'=>$page,'pages'=>(int)($d['total_pages']??1)]);

        case 'search':
            $q=trim((string)($_GET['q']??'')); if(mb_strlen($q)<2) json_response(['ok'=>true,'items'=>[]]);
            $page=max(1,min(20,(int)($_GET['page']??1)));
            $d=tmdb_cached('search_'.sha1($q.'|'.$page),function() use($q,$page){return tmdb_request('search/multi',['query'=>$q,'page'=>$page,'include_adult'=>'false']);},300);
            $items=[]; foreach(($d['results']??[]) as $i){ if(($i['media_type']??'')==='movie'||($i['media_type']??'')==='tv')$items[]=normalize_media($i,$i['media_type']); }
            json_response(['ok'=>true,'items'=>$items,'page'=>$page,'pages'=>(int)($d['total_pages']??1)]);

        case 'details':
            $type=($_GET['type']??'movie')==='tv'?'tv':'movie'; $id=(int)($_GET['id']??0); if(!$id)json_response(['ok'=>false,'message'=>'شناسه نامعتبر است.'],422);
            $d=tmdb_cached("detail_{$type}_{$id}",function() use($type,$id){return tmdb_request($type.'/'.$id,['append_to_response'=>'credits,videos,images']);},21600);
            $d['poster_url']=tmdb_image($d['poster_path']??null,'w500'); $d['backdrop_url']=tmdb_image($d['backdrop_path']??null,'w1280');
            $d['display_title']=$d['title']??$d['name']??''; $d['year']=substr($d['release_date']??$d['first_air_date']??'',0,4);
            json_response(['ok'=>true,'item'=>$d]);

        case 'favorite_toggle':
            $uid=require_login(); require_csrf(); $d=input_json(); $type=($d['type']??'movie')==='tv'?'tv':'movie'; $id=(int)($d['id']??0); if(!$id)json_response(['ok'=>false,'message'=>'شناسه نامعتبر است.'],422);
            $stmt=db()->prepare('SELECT 1 FROM favorites WHERE user_id=? AND media_type=? AND media_id=?');$stmt->execute([$uid,$type,$id]);
            if($stmt->fetch()){ $s=db()->prepare('DELETE FROM favorites WHERE user_id=? AND media_type=? AND media_id=?');$s->execute([$uid,$type,$id]);json_response(['ok'=>true,'favorite'=>false]); }
            $detail=tmdb_request($type.'/'.$id); $title=$detail['title']??$detail['name']??''; $year=(int)substr($detail['release_date']??$detail['first_air_date']??'',0,4);
            $s=db()->prepare('INSERT INTO favorites(user_id,media_type,media_id,title,poster_path,year) VALUES(?,?,?,?,?,?)');$s->execute([$uid,$type,$id,$title,$detail['poster_path']??null,$year?:null]);json_response(['ok'=>true,'favorite'=>true]);

        case 'favorites':
            $uid=require_login(); $s=db()->prepare('SELECT media_type type,media_id id,title,poster_path,year FROM favorites WHERE user_id=? ORDER BY created_at DESC');$s->execute([$uid]);
            $items=[]; foreach($s as $r){$r['poster']=tmdb_image($r['poster_path']??null,'w500');$items[]=$r;} json_response(['ok'=>true,'items'=>$items]);

        case 'history_add':
            $uid=require_login(); require_csrf(); $d=input_json();$type=($d['type']??'movie')==='tv'?'tv':'movie';$id=(int)($d['id']??0);$progress=max(0,(int)($d['progress']??0)); if(!$id)json_response(['ok'=>false,'message'=>'شناسه نامعتبر است.'],422);
            $detail=tmdb_request($type.'/'.$id);$title=$detail['title']??$detail['name']??'';
            $s=db()->prepare('INSERT INTO watch_history(user_id,media_type,media_id,title,poster_path,progress_seconds) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),poster_path=VALUES(poster_path),progress_seconds=VALUES(progress_seconds),watched_at=CURRENT_TIMESTAMP');$s->execute([$uid,$type,$id,$title,$detail['poster_path']??null,$progress]);json_response(['ok'=>true]);

        case 'history':
            $uid=require_login();$s=db()->prepare('SELECT media_type type,media_id id,title,poster_path,progress_seconds,watched_at FROM watch_history WHERE user_id=? ORDER BY watched_at DESC LIMIT 24');$s->execute([$uid]);$items=[];foreach($s as $r){$r['poster']=tmdb_image($r['poster_path']??null,'w500');$items[]=$r;}json_response(['ok'=>true,'items'=>$items]);

        default: json_response(['ok'=>false,'message'=>'عملیات نامعتبر است.'],404);
    }
} catch (Throwable $e) {
    error_log('[MrRashidi] '.$e->getMessage());
    json_response(['ok'=>false,'message'=>'خطای داخلی یا اتصال به سرویس اطلاعات فیلم. تنظیمات سرور را بررسی کنید.'],500);
}
