(function () {
    if ( ! window.MANTRAM_AUDIO_URL) {
        return;
    }

    const audio = new Audio(MANTRAM_AUDIO_URL);
    const playButton = document.getElementById('audio-play');
    const pauseButton = document.getElementById('audio-pause');
    const audioSeek = document.getElementById('audio-seek');
    const seekProgress = audioSeek.querySelector('div');

    playButton.addEventListener('click', function () {
        audioSeek.classList.remove('hidden');
        audio.play();
        playButton.classList.add('hidden');
        pauseButton.classList.remove('hidden');
    });

    pauseButton.addEventListener('click', function () {
        audioSeek.classList.add('hidden');
        audio.currentTime = 0;
        audio.pause();
        playButton.classList.remove('hidden');
        pauseButton.classList.add('hidden');
    });

    audio.addEventListener('ended', function () {
        audioSeek.classList.add('hidden');
        playButton.classList.remove('hidden');
        pauseButton.classList.add('hidden');
    });

    audio.addEventListener('timeupdate', function () {
        const duration = this.duration;
        const currentTime = this.currentTime;

        seekProgress.style.width = `${currentTime / duration * 100}%`;
    });
})();
