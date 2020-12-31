function __ValPressTextEditor() {
    this._editors = {};
}

__ValPressTextEditor.prototype.register = function (id, quill) {
    this._editors[id] = quill;
};
__ValPressTextEditor.prototype.getHTML = function (id) {
    return (this._editors[id] ? this._editors[id].root.innerHTML : '');
};
__ValPressTextEditor.prototype.clear = function (id) {
    this._editors[id].root.innerHTML = '';
    return this;
};


window.ValPressTextEditor = new __ValPressTextEditor();
