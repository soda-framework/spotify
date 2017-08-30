var Vue = require('vue');

if( $('#tracks').length ) {
    var tracks_vm = new Vue({
        el: '#tracks',
        data: {
            playlist: null,
            playlists: [],
            loading: false,
            tracks: [],
            current_track: 0,

            audio: null,
            audio_track: null,

            playing: false,
            ready_for_next: true,
            token: null,
        },
        created: function(){
            var me = this;

            me.token = document.head.querySelector("[name=csrf-token]").content;
            me.playlist = document.head.querySelector("[name=playlist]").content;
            me.post_get_tracks();
        },
        methods: {
            init: function () {
                var me = this;

                // get audio objects
                if ($('#audio').length && me.audio == null) {
                    me.audio = $('#audio').get(0);
                    me.audio_track = me.tracks[me.current_track].preview_url;

                    me.audio.onended = function () {
                        me.next_track();
                    };
                }

                if( me.playing ){
                    me.play_track();
                }
            },
            change_playlist: function(playlist){
                var me = this;

                // only change if not already on this
                if( me.playlist != playlist ){
                    me.playlist = playlist;
                    me.post_get_tracks();token

                    // Send event
                    ga('send', 'event', 'Playlist', 'Playlist Changed', playlist);

                    return true; // return true, that yes, a change is occuring
                }
                else{
                    return false; // no change necessary
                }
            },
            /**
             * Pre-populate the tracks list
             */
            post_get_tracks: function () {
                var me = this;

                var data = {
                    _token: me.token,
                };

                me.loading = true;
                $.post('/spotify/api/tracks/'+me.playlist, data, function (data) {
                    me.current_track = 0;
                    me.audio = null;
                    me.audio_track = null;

                    if (data.success) {
                        me.tracks = data.tracks;

                        me.init();
                    }

                    me.loading = false;
                });
            },
            nextTrackIndex: function (index) {
                var me = this;

                if (index >= me.tracks.length - 1) return 0; // > 2 as to include the last element
                else return index + 1;
            },
            prevTrackIndex: function (index) {
                var me = this;

                if (index <= 0) return me.tracks.length - 1;
                else return index - 1;
            },
            next_track: function () {
                var me = this;

                if (me.ready_for_next) {
                    var track = me.current_track;
                    var audio_track = me.audio_track;

                    // Skip tracks with no preview
                    do {
                        track = me.nextTrackIndex(track);
                        audio_track = me.tracks[track].preview_url;
                    } while (audio_track && audio_track.length <= 0);

                    me.current_track = track;
                    me.audio_track = audio_track;

                    if (me.playing) {
                        me.play_track();
                    }

                    // Send event
                    ga('send', 'event', 'Playlist', 'Next Track', me.playlist);
                }
            },
            prev_track: function () {
                var me = this;

                if (me.ready_for_next) {
                    var track = me.current_track;
                    var audio_track = me.audio_track;

                    // Skip tracks with no preview
                    do {
                        track = me.prevTrackIndex(track);
                        audio_track = me.tracks[track].preview_url;
                    } while (audio_track && audio_track.length <= 0);

                    me.current_track = track;
                    me.audio_track = audio_track;

                    if (me.playing) {
                        me.play_track();
                    }

                    // Send event
                    ga('send', 'event', 'Playlist', 'Previous Track', me.playlist);
                }
            },
            play_track: function () {
                var me = this;

                if( me.audio ) {
                    me.audio.src = me.audio_track;
                    me.audio.load();
                    me.audio.play();

                    // Send event
                    ga('send', 'event', 'Playlist', 'Play Track', me.playlist);
                }

                me.playing = true;

            },
            pause_track: function () {
                var me = this;

                if( me.audio ) {
                    me.audio.pause();

                    // Send event
                    ga('send', 'event', 'Playlist', 'Pause Track', me.playlist);
                }

                me.playing = false;

            },
            toggle_track_playing: function (playlist) {
                var me = this;

                // behave normally if no change is occuring
                if( ! me.change_playlist(playlist) ) {
                    if (me.playing) {
                        me.pause_track();
                    }
                    else {
                        me.play_track();
                    }
                }
                else{
                    me.playing = true;
                }
            }
        }
    });
}
