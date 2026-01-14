const config = require('../../config')

export const getAdminKey = () => {
  return localStorage.getItem('admin_key');
}

export const setAdminKey = (key) => {
  localStorage.setItem('admin_key', key);
}

export const getPosterName = () => {
  const poster = localStorage.getItem('poster')

  return (poster === 'undefined' || poster === null) ? config.default_poster : poster;
}

export const setPosterName = (name) => {
  localStorage.setItem('poster', name);
}

export const setPostPassword = (id, password) => {
  localStorage.setItem(id, password);
}

export const getPostPassword = (id) => {
  return localStorage.getItem(id);
}
