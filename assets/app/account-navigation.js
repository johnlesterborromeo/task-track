/* ================= Navigation ================= */
const sidebar = $('.sidebar');
const sidebarMenuToggle = $('#sidebar-menu-toggle');
const profileToggle = $('#profile-toggle');
const accountMenu = $('#account-menu');
function positionAccountMenu() {
  const trigger = profileToggle.getBoundingClientRect();
  const menuWidth = accountMenu.offsetWidth;
  const menuHeight = accountMenu.offsetHeight;
  const left = Math.max(8, Math.min(trigger.right - menuWidth, window.innerWidth - menuWidth - 8));
  let top = trigger.bottom + 6;
  if (top + menuHeight > window.innerHeight - 8) top = trigger.top - menuHeight - 6;
  top = Math.max(8, Math.min(top, window.innerHeight - menuHeight - 8));
  accountMenu.style.left = `${left}px`;
  accountMenu.style.top = `${top}px`;
}
$$('.logout').forEach(link => link.addEventListener('click', event => {
  if (!confirm('Are you sure you want to log out?')) event.preventDefault();
}));
profileToggle.addEventListener('click', () => {
  accountMenu.hidden = !accountMenu.hidden;
  profileToggle.setAttribute('aria-expanded', String(!accountMenu.hidden));
  if (!accountMenu.hidden && window.matchMedia('(max-width: 760px)').matches) positionAccountMenu();
});
window.addEventListener('resize', () => {
  if (!accountMenu.hidden && window.matchMedia('(max-width: 760px)').matches) positionAccountMenu();
});
window.addEventListener('scroll', () => {
  if (!accountMenu.hidden && window.matchMedia('(max-width: 760px)').matches) positionAccountMenu();
}, true);
document.addEventListener('click', event => {
  if (!event.target.closest('.profile-control')) {
    accountMenu.hidden = true;
    profileToggle.setAttribute('aria-expanded', 'false');
  }
});
async function getAccountProfile() {
  const profile = await api('profile');
  if (profile.error) throw new Error(profile.error);
  return profile;
}
function applyProfilePicture(hasPicture) {
  const imageUrl = hasPicture ? `profile_picture.php?v=${Date.now()}` : '';
  const avatars = [
    { root: $('.profile-avatar'), fallback: $('.profile-avatar svg'), image: $('#sidebar-profile-picture') },
    { root: $('#profile-info-avatar'), fallback: $('.account-avatar-fallback', $('#profile-info-avatar')), image: $('img', $('#profile-info-avatar')) },
    { root: $('#account-avatar'), fallback: $('.account-avatar-fallback', $('#account-avatar')), image: $('img', $('#account-avatar')) },
    { root: $('#profile-picture-preview'), fallback: $('.account-avatar-fallback', $('#profile-picture-preview')), image: $('img', $('#profile-picture-preview')) }
  ];
  avatars.forEach(({ root, fallback, image }) => {
    image.hidden = !hasPicture;
    fallback.hidden = hasPicture;
    if (hasPicture) image.src = imageUrl;
    else image.removeAttribute('src');
  });
  $('#remove-profile-picture').hidden = !hasPicture;
}
function setAvatarInitial(selector, name) {
  $(`.account-avatar-fallback`, $(selector)).textContent = Array.from(name.trim())[0]?.toUpperCase() || '?';
}
function closeAccountMenu() {
  accountMenu.hidden = true;
  profileToggle.setAttribute('aria-expanded', 'false');
}
function selectAccountTab(tab) {
  const profileSelected = tab === 'profile';
  $('#profile-tab').classList.toggle('active', profileSelected);
  $('#profile-tab').setAttribute('aria-selected', String(profileSelected));
  $('#profile-tab').tabIndex = profileSelected ? 0 : -1;
  $('#security-tab').classList.toggle('active', !profileSelected);
  $('#security-tab').setAttribute('aria-selected', String(!profileSelected));
  $('#security-tab').tabIndex = profileSelected ? -1 : 0;
  $('#profile-form').hidden = !profileSelected;
  $('#password-form').hidden = profileSelected;
}
async function showProfileInformation() {
  closeAccountMenu();
  $('#profile-info-error').hidden = true;
  $('#profile-info-name').textContent = $('.user-name').textContent;
  $('#profile-info-username').textContent = `@${profileToggle.dataset.username}`;
  $('#profile-info-bio').textContent = 'Loading profile information...';
  setAvatarInitial('#profile-info-avatar', $('.user-name').textContent);
  openModal('#profile-information-modal');
  try {
    const profile = await getAccountProfile();
    $('#profile-info-name').textContent = profile.display_name;
    $('#profile-info-username').textContent = `@${profile.username}`;
    $('#profile-info-bio').textContent = profile.bio || 'No bio added yet.';
    setAvatarInitial('#profile-info-avatar', profile.display_name);
    applyProfilePicture(profile.has_profile_picture);
  } catch (error) {
    $('#profile-info-bio').textContent = '';
    $('#profile-info-error').textContent = error.message || 'Could not load profile information. Please try again.';
    $('#profile-info-error').hidden = false;
  }
}
async function showManageAccount() {
  closeAccountMenu();
  selectAccountTab('profile');
  $('#profile-form').reset();
  $('#password-form').reset();
  $('#profile-error').hidden = true;
  $('#profile-success').hidden = true;
  $('#password-error').hidden = true;
  $('#password-success').hidden = true;
  $('#profile-username').value = profileToggle.dataset.username;
  $('#profile-display-name').value = $('.user-name').textContent;
  $('#account-identity-name').textContent = $('.user-name').textContent;
  $('#account-identity-username').textContent = '';
  setAvatarInitial('#account-avatar', $('.user-name').textContent);
  setAvatarInitial('#profile-picture-preview', $('.user-name').textContent);
  $$('#profile-form input, #profile-form textarea, #profile-form button').forEach(control => { control.disabled = true; });
  openModal('#account-modal');
  try {
    const profile = await getAccountProfile();
    $('#profile-username').value = profile.username;
    $('#profile-display-name').value = profile.display_name;
    $('#profile-bio').value = profile.bio || '';
    updateAccountIdentity(profile.display_name, profile.username);
    applyProfilePicture(profile.has_profile_picture);
    $$('#profile-form input, #profile-form textarea, #profile-form button').forEach(control => { control.disabled = false; });
  } catch (error) {
    $('#profile-error').textContent = error.message || 'Could not load account details. Please try again.';
    $('#profile-error').hidden = false;
  }
}
$('#show-profile-information').addEventListener('click', showProfileInformation);
$('#manage-account').addEventListener('click', showManageAccount);
$$('[data-account-tab]').forEach(tab => tab.addEventListener('click', () => {
  selectAccountTab(tab.dataset.accountTab);
}));
$$('[data-toggle-password]').forEach(toggle => toggle.addEventListener('click', () => {
  const input = document.getElementById(toggle.dataset.togglePassword);
  const isVisible = input.type === 'password';
  input.type = isVisible ? 'text' : 'password';
  toggle.setAttribute('aria-label', `${isVisible ? 'Hide' : 'Show'} ${input.labels[0].textContent.toLocaleLowerCase()}`);
  toggle.setAttribute('aria-pressed', String(isVisible));
}));
$('#profile-info-manage').addEventListener('click', () => {
  closeModals();
  showManageAccount();
});
$('#profile-form').addEventListener('submit', async event => {
  event.preventDefault();
  $('#profile-error').hidden = true;
  $('#profile-success').hidden = true;
  const username = $('#profile-username').value.trim();
  const displayName = $('#profile-display-name').value.trim();
  const bio = $('#profile-bio').value.trim();
  try {
    const result = await send('update_profile', {
      username,
      display_name: displayName,
      bio
    });
    if (result.error) {
      $('#profile-error').textContent = result.error;
      $('#profile-error').hidden = false;
      return;
    }
    updateAccountIdentity(result.display_name, result.username);
    $('#profile-info-name').textContent = result.display_name;
    $('#profile-info-username').textContent = `@${result.username}`;
    $('#profile-info-bio').textContent = result.bio || 'No bio added yet.';
    setAvatarInitial('#profile-info-avatar', result.display_name);
    $('#profile-success').hidden = false;
  } catch (error) {
    $('#profile-error').textContent = 'Could not update profile. Please try again.';
    $('#profile-error').hidden = false;
  }
});
$('#profile-picture-input').addEventListener('change', async event => {
  const input = event.currentTarget;
  const file = input.files[0];
  if (!file) return;
  $('#profile-picture-error').hidden = true;
  $('#profile-picture-success').hidden = true;
  const formData = new FormData();
  formData.append('profile_picture', file);
  input.disabled = true;
  $('#remove-profile-picture').disabled = true;
  try {
    const result = await sendFile('upload_profile_picture', formData);
    if (result.error) {
      $('#profile-picture-error').textContent = result.error;
      $('#profile-picture-error').hidden = false;
      return;
    }
    applyProfilePicture(true);
    $('#profile-picture-success').hidden = false;
  } catch (error) {
    $('#profile-picture-error').textContent = 'Could not upload profile picture. Please try again.';
    $('#profile-picture-error').hidden = false;
  } finally {
    input.disabled = false;
    $('#remove-profile-picture').disabled = false;
    input.value = '';
  }
});
$('#remove-profile-picture').addEventListener('click', async () => {
  $('#profile-picture-error').hidden = true;
  $('#profile-picture-success').hidden = true;
  $('#profile-picture-input').disabled = true;
  $('#remove-profile-picture').disabled = true;
  try {
    const result = await send('remove_profile_picture', {});
    if (result.error) {
      $('#profile-picture-error').textContent = result.error;
      $('#profile-picture-error').hidden = false;
      return;
    }
    applyProfilePicture(false);
    $('#profile-picture-success').textContent = 'Profile picture removed.';
    $('#profile-picture-success').hidden = false;
  } catch (error) {
    $('#profile-picture-error').textContent = 'Could not remove profile picture. Please try again.';
    $('#profile-picture-error').hidden = false;
  } finally {
    $('#profile-picture-input').disabled = false;
    $('#remove-profile-picture').disabled = false;
  }
});
getAccountProfile()
  .then(profile => applyProfilePicture(profile.has_profile_picture))
  .catch(error => console.error('Could not initialize profile picture:', error));
function updateAccountIdentity(displayName, username) {
  const name = displayName || username;
  $('.user-name').textContent = name;
  profileToggle.dataset.username = username;
  profileToggle.setAttribute('aria-label', `View account details for ${name}`);
  $('#account-identity-name').textContent = name;
  $('#account-identity-username').textContent = `@${username}`;
  setAvatarInitial('#account-avatar', name);
  setAvatarInitial('#profile-info-avatar', name);
  setAvatarInitial('#profile-picture-preview', name);
}
$('#password-form').addEventListener('submit', async event => {
  event.preventDefault();
  $('#password-error').hidden = true;
  $('#password-success').hidden = true;
  const currentPassword = $('#current-password').value;
  const newPassword = $('#new-password').value;
  const confirmPassword = $('#confirm-password').value;
  try {
    const result = await send('update_password', {
      current_password: currentPassword,
      new_password: newPassword,
      confirm_password: confirmPassword
    });
    if (result.error) {
      $('#password-error').textContent = result.error;
      $('#password-error').hidden = false;
      return;
    }
    $('#password-form').reset();
    $('#password-success').hidden = false;
  } catch (error) {
    $('#password-error').textContent = 'Could not update password. Please try again.';
    $('#password-error').hidden = false;
  }
});
$('#brand-home').addEventListener('click', () => showView('dashboard'));
sidebarMenuToggle.addEventListener('click', () => {
  const isOpen = sidebar.classList.toggle('mobile-nav-open');
  sidebarMenuToggle.setAttribute('aria-expanded', String(isOpen));
  sidebarMenuToggle.setAttribute('aria-label', isOpen ? 'Hide navigation' : 'Show navigation');
});

$$('.nav-btn').forEach(btn => btn.addEventListener('click', () => {
  showView(btn.dataset.view);
  sidebar.classList.remove('mobile-nav-open');
  sidebarMenuToggle.setAttribute('aria-expanded', 'false');
  sidebarMenuToggle.setAttribute('aria-label', 'Show navigation');
}));
$('#dash-see-tasks').addEventListener('click', () => showView('tasks'));
$('#dash-see-history').addEventListener('click', () => showView('history'));
$('#dash-see-notes').addEventListener('click', () => showView('notes'));

function showView(view) {
  if (!$$('.view').some(section => section.id === 'view-' + view)) {
    view = 'dashboard';
  }
  state.view = view;
  sessionStorage.setItem('tasktrack-current-view', view);
  $$('.nav-btn').forEach(b => b.classList.toggle('active', b.dataset.view === view));
  $$('.view').forEach(s => s.classList.toggle('active', s.id === 'view-' + view));
  const title = $('#view-title');
  title.textContent = view.charAt(0).toUpperCase() + view.slice(1);
  title.classList.toggle('dashboard-title', view === 'dashboard');
  loadView();
}

function loadView() {
  if (state.view === 'dashboard') return loadDashboard();
  if (state.view === 'tasks')     return loadTasks();
  if (state.view === 'notes')     return loadNotes();
  if (state.view === 'calendar')  return loadCalendar();
  if (state.view === 'history')   return loadHistory();
}
