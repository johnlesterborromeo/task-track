function materialIcon(name, className = '') {
  if (!document.getElementById(`material-icon-${name}`)) {
    throw new Error(`Unknown Material icon: ${name}`);
  }
  const classes = ['material-icon', className].filter(Boolean).join(' ');
  return `<svg class="${classes}" viewBox="0 0 24 24" aria-hidden="true"><use href="#material-icon-${name}"></use></svg>`;
}
