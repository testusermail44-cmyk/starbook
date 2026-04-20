let eject = document.querySelector(".eject");
let info = document.querySelector(".info-panel");
const width = info.offsetWidth;
info.style.opacity = "0";
info.style.right = `${-width}px`;
let right = true;
eject.style.right = `0`;
info.style.transition = "right 0.5s ease, opacity 0.5s ease";
eject.style.transition = "right 0.5s ease";
eject.addEventListener("click", () => {
  if (right) {
    info.style.right = `0`;
    eject.style.right = `${width}px`;
    eject.classList.toggle("rotated");
    info.style.opacity = "1";
    right = false;
  } else {
    info.style.right = `${-width}px`;
    eject.style.right = `0`;
    eject.classList.toggle("rotated");
    info.style.opacity = "0";
    right = true;
  }
});
