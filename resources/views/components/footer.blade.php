<footer class="footer" style="background-image: url('UserLTE/assets/images/about-header-bg.jpg')">

    <button id="scroll-top" title="Back to Top"><i class="icon-arrow-up"></i></button>
    <div class="footer-middle">
        <div class="container">
            <div class="row">
                <div class="col-sm-6 col-lg-3">
                    <div class="widget widget-about">
                        <img src="{{ asset('UserLTE/assets/images/demos/demo-3/Logo.jpg') }}" class="footer-logo"
                            alt="Footer Logo" width="105" height="25" style="border-radius: 50px">
                        <p> NGO TAN LOI Digital Technologies Mang lại trải nghiệm mua sắm hơn cả tuyệt vời!</p>

                        <div class="widget-call">
                            <i class="icon-phone"></i>
                            Có một câu hỏi? Gọi cho chúng tôi 24/7
                            <a href="tel:#">033 712 0073</a>
                        </div><!-- End .widget-call -->
                    </div><!-- End .widget about-widget -->
                </div><!-- End .col-sm-6 col-lg-3 -->

                <div class="col-sm-6 col-lg-3">
                    <div class="widget">
                        <ul class="widget-list">
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống bảo hành</a></li>
                            <li><a href="#">Giới thiệu máy đổi trả</a></li>
                            <li><a href="#">Tin khuyến mãi</a></li>
                        </ul><!-- End .widget-list -->
                    </div><!-- End .widget -->
                </div><!-- End .col-sm-6 col-lg-3 -->

                <div class="col-sm-6 col-lg-3">
                    <div class="widget">
                        <ul class="widget-list">
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống bảo hành</a></li>
                            <li><a href="#">Giới thiệu máy đổi trả</a></li>
                            <li><a href="#">Tin khuyến mãi</a></li>
                        </ul><!-- End .widget-list -->
                    </div><!-- End .widget -->
                </div><!-- End .col-sm-6 col-lg-3 -->

                <div class="col-sm-6 col-lg-3">
                    <div class="widget">
                        <ul class="widget-list">
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống cửa hàng</a></li>
                            <li><a href="#">Hệ thống bảo hành</a></li>
                            <li><a href="#">Giới thiệu máy đổi trả</a></li>
                            <li><a href="#">Tin khuyến mãi</a></li>
                        </ul><!-- End .widget-list -->
                    </div><!-- End .widget -->
                </div><!-- End .col-sm-6 col-lg-3 -->
            </div><!-- End .row -->
        </div><!-- End .container -->
    </div><!-- End .footer-middle -->

    <div class="footer-bottom">
        <div class="container">
            <p class="footer-copyright">{{ getConfigValueFromSettingTable('Copyright') }}</p>
            <!-- End .footer-copyright -->
            <figure class="footer-payments">
                <img src="{{ asset('UserLTE/assets/images/payments.png') }}" alt="Payment methods" width="272"
                    height="20">
            </figure><!-- End .footer-payments -->
        </div><!-- End .container -->
    </div><!-- End .footer-bottom -->

</footer><!-- End .footer -->
<!-- Biểu tượng Chatbot và khung chứa iframe -->
<div class="chatbot-icon-container" id="chatbot-icon-container">
    <div id="chatbot-tooltip" class="chatbot-tooltip">Fashion Studio xin chào!</div>
    <img src="{{ asset('UserLTE/assets/images/chatbot/ai2.png') }}" alt="Chatbot" class="chatbot-icon" id="chatbot-icon"
        width="60px" height="" style=" cursor: pointer;">
</div>
<div id="chatbot-frame-container" style="display: none;">
    {{-- chatbot ngotanloi2424 --}}
    <iframe src="https://app.chatfly.co/chat/98506d2a-e1b8-4a87-bf68-fa64c6a53499" width="450" height="550"
        style="border:1px solid black; border-radius: 10px;"></iframe>

</div>

{{-- Chat Fly AI --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    var chatbotIcon = document.getElementById("chatbot-icon");
    var chatbotFrameContainer = document.getElementById("chatbot-frame-container");
    var chatbotTooltip = document.getElementById("chatbot-tooltip");

    chatbotIcon.addEventListener("click", function() {
        if (chatbotFrameContainer.style.display === "none" || chatbotFrameContainer.style
            .display === "") {
            chatbotFrameContainer.style.display = "block";
        } else {
            chatbotFrameContainer.style.display = "none";
        }

        chatbotIcon.classList.toggle("shaking");
    });

    chatbotIcon.addEventListener("mouseenter", function() {
        chatbotTooltip.style.display = "block";
    });

    chatbotIcon.addEventListener("mouseleave", function() {
        chatbotTooltip.style.display = "none";
    });
});
</script>
<script>
window.chatbotConfig = {
    bot_id: "98506d2a-e1b8-4a87-bf68-fa64c6a53499",
};
</script>
<script src="https://app.chatfly.co/Chat.js"></script>

<style>
.chatbot-icon-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    align-items: center;
}

#chatbot-frame-container {
    position: fixed;
    bottom: 90px;
    right: 20px;
    z-index: 1000;
    background: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

@keyframes shake {
    0% {
        transform: translateX(0);
    }

    20% {
        transform: translateX(-10px);
    }

    40% {
        transform: translateX(10px);
    }

    60% {
        transform: translateX(-10px);
    }

    80% {
        transform: translateX(10px);
    }

    100% {
        transform: translateX(0);
    }
}

.shaking {
    animation: shake 0.5s;
}

.chatbot-tooltip {
    display: none;
    position: absolute;
    bottom: 10px;
    right: 110%;
    /* Adjust as needed to position the tooltip */
    background-color: #6517ab;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    white-space: nowrap;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    font-size: 14px;
    z-index: 1001;
}
</style>