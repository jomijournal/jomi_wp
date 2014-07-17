<?php while (have_posts()) : the_post(); ?>

<div class="container wrap" role="document" style="padding:0; border: none;">
<style>
.support-us-top{  padding:50px;
                  padding-top:20px;
                  padding-bottom:20px;
                  border-bottom: 1px solid black;
                  background:white}
.support-us-bottom{ padding:50px;
                  padding-top:20px;
                  padding-bottom:20px;
                    background-color: #eee
}
</style>

  <!-- javascript for crypto-currency -->
  <script src="http://coindonationwidget.com/widget/coin.js"></script>

  <div class="support-us">
      <div class="row">
          <div class="col-md-12 column">
              <div class="support-us-top">
                  <div class="panel-body">
                      
            <div style="padding-top:10px;text-align:center;">
            <!-- Bitcoin Donation Button -->
                       <script>
                           CoinWidgetCom.go({
                           wallet_address: "1BDhHiZaWDi8BmHhmF3QjqS92x3sJRDWER"
                               , currency: "bitcoin"
                               , counter: "hide"
                               , language: "en"
                               , decimals: 2
                               });
                       </script>
                       <!-- Litecoin Donation Button -->
                       <script>
                           CoinWidgetCom.go({
                           wallet_address: "LMvh96PAvX3DgPPPCEMfZreKRLF1LTMB7Q"
                               , currency: "litecoin"
                               , counter: "hide"
                               , language: "en"
                               , lbl_amount: "LTC"
                               , decimals: 2
                               });
                       </script>
                       <!-- Peercoin Donation Button -->
                       <script>
                           CoinWidgetCom.go({
                           wallet_address: "PVh6jjS7WTumt8q6a6CYfTGBeZsbdorbAD"
                               , currency: "peercoin"
                               , counter: "hide"
                               , language: "en"
                               , lbl_amount: "PPC"
                               , decimals: 2
                               });
                       </script>
                       <!-- Dogecoin Donation Button -->
                       <script>
                           CoinWidgetCom.go({
                           wallet_address: "DT25wVQs5jCVQ1Bi8AzRzxMvtyGfHnjS6A"
                               , currency: "dogecoin"
                               , counter: "hide"
                               , language: "en"
                               , decimals: 2
                               , lbl_amount: "DOGE"
                               });
                       </script>
                       <!-- Quarkcoin Donation Button -->
                       <script>
                           CoinWidgetCom.go({
                           wallet_address: "Qfkvqd3oEWfd8cCJAvNSHW77P6ZzDGXdQ4"
                               , currency: "quark"
                               , counter: "hide"
                               , language: "en"
                               , lbl_amount: "QRK"
                               , decimals: 2
                               });
                       </script>

            <div style="padding-top:15px">
              <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                       <input type="hidden" name="cmd" value="_s-xclick">
                       <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
                       ">
                       <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                       <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                       </form>
            </div>
            </div>
          </div>
        </div>
                <div class="support-us-bottom">
          
                  <h1 style="margin-bottom:0px">Support Us</h1>
          
            <p><b>Q: Why?</b></p>
            <p>We need your support to help us maximize our impact.  That is, we need to get to self-sustaining levels in revenues without giving control over to professional investors.</p>
            <p><b>Q: What's wrong with professional investors?</b></p>
            <p>They'll spin us up, jack up the prices, and sell us to some big publisher. We don't want that.</p>
            <p><b>Q: What do you want?</b></p>
            <p>To help fix healthcare.</p>
            <p><b>Q: That's nice. Can you be more precise?</b></p>
            <p>We want to go after content and engage in activity to maximize impact as measured in lives affected.  So we might not make the best decisions financially-speaking, and that's fine by us so long as the venture is sustainable.  Example: content and services for medically-underserved communities.</p>
            <p><b>Q: Will the money line your pockets?</b></p>
            <p>No. JoMI operates in a fundamentally different way from any other company you may have come across.  There are two bank accounts: one provides us with an operating budget and the other holds the incoming revenues.  Your money will go directly into the operating budget, which does NOT go towards salaries.  Partners in JoMI (which includes everyone involved) receive distributions from the revenues account using an impact-based model (see <a href="http://www.fairsetup.com">FairSetup</a>).  Until we have revenues sufficient to sustain ourselves, we allow those partners who need to take loans out of the operating budget with the agreement to repay the loans out of the upside generated.</p>
                      <p><b>Q: So what will you spend the money on?</b></p>
                  <p> Growth and to sustain partners who don't have savings through the loan program mentioned above.  </p><p>Operationally: office space, cameras, transportation, conferences, marketing collateral, etc.</p>
                  <p><b>Questions? Concerns?</b></p>
                  <p> <a href="mailto:contact@jomi.com">contact@jomi.com</a></p>
                  </div>
        </div>


          </div>
      </div>
  </div>


  </div>

<?php endwhile; ?>