export function getCurrentDateTime() {
  const now = new Date();
  const pad = (n) => n.toString().padStart(2, '0');

  const yyyy = now.getFullYear();
  const mm = pad(now.getMonth() + 1);
  const dd = pad(now.getDate());
  const hh = pad(now.getHours());
  const min = pad(now.getMinutes());
  const ss = pad(now.getSeconds());

  return `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`;
}