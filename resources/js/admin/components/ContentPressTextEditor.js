function __ContentPressTextEditor() {
    this._editors = {};
}

__ContentPressTextEditor.prototype.register = function (id, quill) {
    this._editors[id] = quill;
};
__ContentPressTextEditor.prototype.getHTML = function (id) {
    return (this._editors[id] ? this._editors[id].root.innerHTML : '');
};
__ContentPressTextEditor.prototype.clear = function (id) {
    this._editors[id].root.innerHTML = '';
    return this;
};


window.ContentPressTextEditor = new __ContentPressTextEditor();
