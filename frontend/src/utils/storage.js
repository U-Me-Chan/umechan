const config = require('../../config')

const cookieName = 'umechan-'

export const getAdminKey = () => {
  return document.cookie.split('; ').find(row => row.startsWith(cookieName + 'admin_key='))?.split('=')[1]
}

export const setAdminKey = (key) => {
  document.cookie = `${cookieName}admin_key=${key}; path=/`
}

export const getPosterName = () => {
  const stored = document.cookie.split('; ').find(row => row.startsWith(cookieName + 'poster='))?.split('=')[1]
  return (stored === undefined || stored === null) ? config.default_poster : stored
}

export const setPosterName = (name) => {
  document.cookie = `${cookieName}poster=${name}; path=/`
}

export const setPostPassword = (id, password) => {
  document.cookie = `${id}=${password}; path=/`
}

export const getPostPassword = (id) => {
  return document.cookie.split('; ').find(row => row.startsWith(id + '='))?.split('=')[1]
}
