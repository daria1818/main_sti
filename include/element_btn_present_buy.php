<button id="open-modal-present-buy" style="
							width: auto;
							padding: 15px 5px 15px 5px;
							max-width: 256px;
							height: 35px;
							border: 1px solid red;
							border-radius: 2px;
							font-size: 13px;
							line-height: 13px;
							color: #FFF;
							background-color: red;
							display: flex;
							align-items: center;
							margin-left:7px;
							margin-top: 0;
						">Купить за 1 024 250 руб.</button>




   
<!-- </div> -->

<script>

const scriptForModal = () => {
  const modal = document.getElementById('modal-present-buy');
  const headerWrapper = document.querySelector('.wrapper1 .header_wrap');

  const btn = document.getElementById("open-modal-present-buy");

  const span = document.getElementsByClassName("close-buy")[0];

  btn.onclick = function() {
    modal.style.display = "block";
    headerWrapper.style.zIndex = 1

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


</script>

<style>
    /* .test-modal-element{
        display: none;
    } */

    .modal-buy {
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

.modal-content-my-buy {
  position: relative;
  width: 100%; 
  max-width: 700px;
  top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.close-buy {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
  z-index: 500;
  display: block;
  position: absolute;
  right: 1%;
}

.close-buy:hover,
.close-buy:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

#open-modal-present-buy{
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

#open-modal-present-buy span{
    margin-left: 16px;
    width: 25px;
    height: 25px;
}

#open-modal-present-buy:hover {
    box-shadow: 0 7px 15px 0 rgb(0 0 0 / 19%);
    border-color: transparent;
}

@media screen and (min-width: 768px) {
    #open-modal-present-buy{
        width: auto;
        height: 48px;
        margin-top: 25px;
        padding: 18px 5px 18px 5px;
    }

    #open-modal-present-buy span{
        margin-left: 9px;
    }
}

@media screen and (min-width: 1024px) {
    #open-modal-present-buy{
        /* width: 161px; */
        height: 33px;
        font-size: 12px;
        line-height:12px;
        
    }

    #open-modal-present-buy span{
        margin-left: 13px;
    }
}

</style>
