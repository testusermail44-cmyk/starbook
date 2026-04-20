function addTitle() {
    const sel = window.getSelection();
    if (!sel.rangeCount) return;
    const range = sel.getRangeAt(0);
    const desc = document.getElementById('description');
    if (!desc.contains(range.commonAncestorContainer)) {
        return;
    }
    const selectedText = range.extractContents();
    const wrapper = document.createElement('div');
    wrapper.className = 'title sr';
    wrapper.appendChild(selectedText);
    range.insertNode(wrapper);
    sel.removeAllRanges();
}
