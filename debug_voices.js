window.speechSynthesis.onvoiceschanged = () => {
    console.log(window.speechSynthesis.getVoices().map(v => v.name + ' - ' + v.lang));
};
