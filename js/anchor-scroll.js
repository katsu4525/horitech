// anchor-scroll.js
document.addEventListener("DOMContentLoaded", function() {
  // 固定ヘッダーの高さを取得
  const header = document.getElementById("header");
  const getHeaderHeight = () => header ? header.offsetHeight : 0;

  // ページ内リンクのクリックイベント
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href').substring(1);
      const targetElement = document.getElementById(targetId);
      if (!targetElement) return;

      e.preventDefault();

      const headerHeight = getHeaderHeight();
      const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;

      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    });
  });

  // ページ読み込み時にハッシュがある場合も補正
  if (window.location.hash) {
    const targetId = window.location.hash.substring(1);
    const targetElement = document.getElementById(targetId);
    if (targetElement) {
      const headerHeight = getHeaderHeight();
      const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
      window.scrollTo({
        top: targetPosition
      });
    }
  }
});
