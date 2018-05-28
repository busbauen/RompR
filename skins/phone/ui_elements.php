<?php

function albumTrack($data) {
    global $prefs;
    if (substr($data['title'],0,6) == "Album:") return 2;
    if (substr($data['title'],0,7) == "Artist:") return 1;

    $d = getDomain($data['uri']);

    if ($prefs['player_backend'] == "mpd" && $d == "soundcloud") {
        $class = 'clickcue';
    } else {
        $class = 'clicktrack';
    }
    $class .= $data['discclass'];

    // Outer container
    if ($data['uri'] == null) {
        print '<div class="clickable '.$class.' ninesix draggable indent containerbox padright calign" name="'.$data['ttid'].'">';
    } else {
        print '<div class="clickable '.$class.' ninesix draggable indent containerbox padright calign" name="'.rawurlencode($data['uri']).'">';
    }

    // Track Number
    if ($data['trackno'] && $data['trackno'] != "" && $data['trackno'] > 0) {
        print '<div class="tracknumber fixed"';
        if ($data['numtracks'] > 99 || $data['trackno'] > 99) {
            print ' style="width:3em"';
        }
        print '>'.$data['trackno'].'</div>';
    }

    print domainIcon($d, 'playlisticon');

    // Track Title, Artist, and Rating
    if ((string) $data['title'] == "") $data['title'] = urldecode($data['uri']);
    print '<div class="expand containerbox vertical">';
    print '<div class="fixed tracktitle">'.$data['title'].'</div>';
    if ($data['artist'] && $data['trackartistindex'] != $data['albumartistindex']) {
        print '<div class="fixed playlistrow2 trackartist">'.$data['artist'].'</div>';
    }
    if ($data['rating']) {
        print '<div class="fixed playlistrow2 trackrating">';
        print '<i class="icon-'.trim($data['rating']).'-stars rating-icon-small"></i>';
        print '</div>';
    }
    if ($data['tags']) {
        print '<div class="fixed playlistrow2 tracktags">';
        print '<i class="icon-tags smallicon"></i>'.$data['tags'];
        print '</div>';
    }
    print '</div>';

    // Track Duration
    print '<div class="fixed playlistrow2 tracktime">';
    if ($data['time'] > 0) {
        print format_time($data['time']);
    }
    print '</div>';

    // Delete Button
    if ($data['lm'] === null) {
        print '<i class="icon-cancel-circled playlisticonr fixed clickable clickicon clickremdb"></i>';
    }

    print '</div>';

    return 0;
}

function artistHeader($id, $name) {
    global $divtype;
    $h = '<div class="menu containerbox menuitem '.
        $divtype.'" name="'.$id.'">';
    $h .= '<div class="expand">'.$name.'</div>';
    $h .= '</div>';
    return $h;
}

function noAlbumsHeader() {
    print '<div class="playlistrow2" style="padding-left:64px">'.
        get_int_text("label_noalbums").'</div>';
}

function albumHeader($obj) {
    global $prefs;
    $h = '';
    if (array_key_exists('plpath', $obj)) {
        $h .= '<input type="hidden" name="dirpath" value="'.$obj['plpath'].'" />';
    }
    $h .= '<div class="menu containerbox menuitem" name="'.$obj['id'].'">';

    $i = $obj['Image'];
    $h .= '<div class="smallcover fixed">';
    $extra = (array_key_exists('userplaylist', $obj)) ? ' plimage' : '';
    if (!$obj['Image'] && $obj['Searched'] != 1) {
        $h .= '<img class="smallcover fixed notexist'.$extra.'" name="'.$obj['ImgKey'].'" />'."\n";
    } else  if (!$obj['Image'] && $obj['Searched'] == 1) {
        $h .= '<img class="smallcover fixed notfound'.$extra.'" name="'.$obj['ImgKey'].'" />'."\n";
    } else {
        $h .= '<img class="smallcover fixed'.$extra.'" name="'.$obj['ImgKey'].'" src="'.$i.'" />'."\n";
    }
    $h .= '</div>';

    if ($obj['AlbumUri']) {
        $d = getDomain($obj['AlbumUri']);
        $d = preg_replace('/\+.*/','', $d);
        $h .= domainIcon($d, 'collectionicon');
        if (strtolower(pathinfo($obj['AlbumUri'], PATHINFO_EXTENSION)) == "cue") {
            $h .= '<i class="icon-doc-text playlisticon fixed"></i>';
        }
    }

    if ($prefs['sortcollectionby'] == 'albumbyartist' && $obj['Artistname']) {
        $h .= '<div class="expand">'.$obj['Albumname'];
        $h .= '<br><span class="notbold">'.$obj['Artistname'].'</span>';
        if ($obj['Year'] && $prefs['sortbydate']) {
            $h .= ' <span class="notbold">('.$obj['Year'].')</span>';
        }
        $h .= '</div>';
    } else {
        $h .= '<div class="expand">'.$obj['Albumname'];
        if ($obj['Year'] && $prefs['sortbydate']) {
            $h .= ' <span class="notbold">('.$obj['Year'].')</span>';
        }
        if ($obj['Artistname']) {
            $h .= '<br><span class="notbold">'.$obj['Artistname'].'</span>';
        }
        $h .= '</div>';
    }
    $h .= '</div>';
    return $h;
}

function albumControlHeader($fragment, $why, $what, $who, $artist) {
    if ($fragment || $who == 'root') {
        return '';
    }
    $html = '<div class="menu backmenu" name="'.$why.'artist'.$who.'">';
    $html .='</div>';
    $html .= '<div class="configtitle textcentre"><b>'.$artist.'</b></div>';
    $html .= '<div class="textcentre clickable clickalbum ninesix" name="'.$why.'artist'.$who.'">'.get_int_text('label_play_all').'</div>';
    return $html;
}

function trackControlHeader($why, $what, $who, $dets) {
    $html = '<div class="menu backmenu" name="'.$why.$what.$who.'"></div>';
    foreach ($dets as $det) {
        $html .= '<div class="album-menu-header"><img class="album_menu_image" src="'.preg_replace('#albumart/small#', 'albumart/asdownloaded', $det['Image']).'" /></div>';
        if ($why != '') {
            $html .= '<div class="textcentre ninesix playlistrow2">'.get_int_text('label_play_options').'</div>';
            $html .= '<div class="containerbox wrap album-play-controls">';
            if ($det['AlbumUri']) {
                $albumuri = rawurlencode($det['AlbumUri']);
                if (strtolower(pathinfo($albumuri, PATHINFO_EXTENSION)) == "cue") {
                    $html .= '<div class="icon-no-response-playbutton collectionicon expand clickable clickcue" name="'.$albumuri.'"></div>';
                } else {
                    $html .= '<div class="icon-no-response-playbutton collectionicon expand clickable clicktrack" name="'.$albumuri.'"></div>';
                }
            } else {
                $html .= '<div class="icon-no-response-playbutton collectionicon expand clickable clickalbum" name="'.$why.'album'.$who.'"></div>';
            }
            $html .= '<div class="icon-single-star collectionicon expand clickable clickicon clickable clickalbum" name="ralbum'.$who.'"></div>';
            $html .= '<div class="icon-tags collectionicon expand clickable clickicon clickable clickalbum" name="talbum'.$who.'"></div>';
            $html .= '<div class="icon-ratandtag collectionicon expand clickable clickicon clickable clickalbum" name="yalbum'.$who.'"></div>';
            $html .= '<div class="icon-ratortag collectionicon expand clickable clickicon clickable clickalbum" name="ualbum'.$who.'"></div>';
            $html .= '</div>';
            $html .= '<div class="textcentre ninesix playlistrow2">'.ucfirst(get_int_text('label_tracks')).'</div>';
        }
    }
    print $html;
}

function printDirectoryItem($fullpath, $displayname, $prefix, $dircount, $printcontainer = false) {
    $c = ($printcontainer) ? "searchdir" : "directory";
    print '<input type="hidden" name="dirpath" value="'.rawurlencode($fullpath).'" />';
    print '<div class="'.$c.' menu containerbox menuitem" name="'.$prefix.$dircount.'">';
    print '<i class="icon-folder-open-empty fixed smallicon"></i>';
    print '<div class="expand">'.htmlentities(urldecode($displayname)).'</div>';
    print '</div>';
    if ($printcontainer) {
        print '<div class="dropmenu" id="'.$prefix.$dircount.'"><div class="menu backmenu" name="'.$prefix.$dircount.'"></div>';
    }
}

function directoryControlHeader($prefix, $name = null) {
    print '<div class="menu backmenu" name="'.trim($prefix, '_').'"></div>';
    if ($name !== null) {
        print '<div class="textcentre"><b>'.$name.'</b></div>';
    }
}

function printRadioDirectory($att) {
    $name = md5($att['URL']);
    print '<input type="hidden" value="'.rawurlencode($att['URL']).'" />';
    print '<input type="hidden" value="'.rawurlencode($att['text']).'" />';
    print '<div class="browse menu directory containerbox menuitem" name="tunein_'.$name.'">';
    print '<i class="icon-folder-open-empty fixed smallicon"></i>';
    print '<div class="expand">'.$att['text'].'</div>';
    print '</div>';
    print '<div id="tunein_'.$name.'" class="dropmenu"></div>';
}

function playlistPlayHeader($name) {
    print '<div class="textcentre clickable clickloadplaylist ninesix" name="'.$name.'">'.get_int_text('label_play_all');
    print '<input type="hidden" name="dirpath" value="'.$name.'" />';
    print '</div>';
}

?>