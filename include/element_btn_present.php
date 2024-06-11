<!-- <div   id='test-div-element' class="test-modal-element"> -->

<button id="open-modal-present">Заказать презентацию <span>
<svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M5.45658 11.2715C7.51373 15.3143 10.828 18.6143 14.8709 20.6857L18.0137 17.5429C18.3994 17.1572 18.9709 17.0286 19.4709 17.2C21.0709 17.7286 22.7994 18.0143 24.5709 18.0143C25.3566 18.0143 25.9994 18.6572 25.9994 19.4429V24.4286C25.9994 25.2143 25.3566 25.8572 24.5709 25.8572C11.1566 25.8572 0.285156 14.9857 0.285156 1.57145C0.285156 0.78574 0.928013 0.142883 1.71373 0.142883H6.71373C7.49944 0.142883 8.1423 0.78574 8.1423 1.57145C8.1423 3.35717 8.42801 5.07145 8.95658 6.67145C9.11373 7.17145 8.99944 7.7286 8.59944 8.1286L5.45658 11.2715Z" fill="#FF0000"/>
</svg>

</span></button>

<div id="modal-present" class="modal">
  <div class="modal-content-my">
    <span class="close">&times;</span>
    <script data-b24-form="inline/12/owolpj" data-skip-moving="true">
    (function(w,d,u){
        var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/180000|0);
        var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
    })(window,document,'https://crm.stionline.ru/upload/crm/form/loader_12_owolpj.js');
    </script>
  </div>
</div>



   
<!-- </div> -->

<script>

const scriptForModal = () => {
  const modal = document.getElementById('modal-present');
  const headerWrapper = document.querySelector('.wrapper1 .header_wrap');

  const btn = document.getElementById("open-modal-present");

  const span = document.getElementsByClassName("close")[0];

  btn.onclick = function() {
    modal.style.display = "block";
    headerWrapper.style.zIndex = 1
    console.log();
  }

  span.onclick = function() {
    modal.style.display = "none";
    headerWrapper.style.zIndex = 4;
  }

  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
      headerWrapper.style.zIndex = 4;
    }
  }
}

scriptForModal()

//     var divElement = document.getElementById("test-div-element"); 

// var altKeyPressed = false;
// var timer;

// document.addEventListener("keydown", function(event) {
//   if (event.key === "Alt" && !event.repeat) {
//     altKeyPressed = true;
//     timer = setTimeout(function() {
//       if (altKeyPressed) {
//         divElement.classList.remove("test-modal-element");
//       }
//     }, 3000); // 3 секунды (3000 миллисекунд)
//   }
// });

// document.addEventListener("keyup", function(event) {
//   if (event.key === "Alt") {
//     altKeyPressed = false;
//     clearTimeout(timer);
//   }
// });

// document.addEventListener("blur", function() {
//   altKeyPressed = false;
//   clearTimeout(timer);
// });
</script>

<style>
    /* .test-modal-element{
        display: none;
    } */

    .modal {
  display: none; 
  /* overflow: auto;  */
  background-color: rgba(0,0,0,0.4); 
  height: 100%; 
  width: 100%; 
  position: fixed; 
  left: 0px; 
  top: 0px; 
  z-index: 2999;
}

.modal-content-my {
  position: relative;
  width: 100%; 
  max-width: 700px;
  top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  z-index: 500;
  display: block;
  position: absolute;
  right: 1%;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

#open-modal-present{
    width: 100%;
    max-width: 256px;
    height: 40px;
    border: 1px solid #F2F2F2;
    border-radius: 2px;
    font-family: 'PT Sans Caption';
    font-style: normal;
    font-weight: 400;
    font-size: 13px;
    line-height: 13px;
    text-align: center;
    color: #1D2D3E;
    background-color: #fff;
    margin-top: 12px;
    display: flex;
    align-items: center;
    padding: 13px 25px;
}

#open-modal-present span{
    margin-left: 16px;
    width: 25px;
    height: 25px;
}

#open-modal-present:hover {
    box-shadow: 0 7px 15px 0 rgb(0 0 0 / 19%);
    border-color: transparent;
}

@media screen and (min-width: 768px) {
    #open-modal-present{
        width: auto;
        height: 48px;
        margin-top: 25px;
        padding: 18px 5px 18px 5px;
    }

    #open-modal-present span{
        margin-left: 9px;
    }
}

@media screen and (min-width: 1024px) {
    #open-modal-present{
        /* width: 161px; */
        height: 33px;
        font-size: 12px;
        line-height:12px;
        
    }

    #open-modal-present span{
        margin-left: 13px;
    }
}

</style>