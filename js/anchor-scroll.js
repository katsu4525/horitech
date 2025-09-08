// anchor-scroll.js
document.addEventListener("DOMContentLoaded", function() {
  // �Œ�w�b�_�[�̍������擾
  const header = document.getElementById("header");
  const getHeaderHeight = () => header ? header.offsetHeight : 0;

  // �y�[�W�������N�̃N���b�N�C�x���g
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

  // �y�[�W�ǂݍ��ݎ��Ƀn�b�V��������ꍇ���␳
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
