/* ================= Notes ================= */
async function loadNotes() {
  state.notes = await api('notes');
  $('#note-count').textContent = state.notes.length + ' note' + (state.notes.length === 1 ? '' : 's');
  $('#notes-grid').innerHTML = state.notes.length
    ? state.notes.map(n => `<div class="note-card" data-id="${n.id}">
        <div class="note-title">${esc(n.title)}</div>
        <div class="note-body">${esc(n.content || '')}</div>
      </div>`).join('')
    : '<p class="empty">No notes yet. Jot something down!</p>';
}
