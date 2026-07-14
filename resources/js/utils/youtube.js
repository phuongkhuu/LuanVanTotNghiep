export function getYouTubeEmbedUrl(url) {
  if (!url) return null;
  const regex = /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s?#]+)/;
  const match = url.match(regex);
  if (match && match[1]) {
    return `https://www.youtube.com/embed/${match[1]}`;
  }
  return null;
}

export function isYouTubeUrl(url) {
  return url && /(youtube\.com|youtu\.be)/i.test(url);
}

export function getYouTubeThumbnail(url) {
  const embed = getYouTubeEmbedUrl(url);
  if (embed) {
    const match = embed.match(/embed\/([^?]+)/);
    if (match) return `https://img.youtube.com/vi/${match[1]}/hqdefault.jpg`;
  }
  return null;
}