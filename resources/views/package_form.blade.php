<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
      integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
      crossorigin="anonymous"
    />
    <title>Package Inquiry</title>
  </head>
  <body>
    <div class="container px-6 py-5">
      <h2 class="text-center">Webview</h2>
    </div>
    <script>
      (function(d, s, id) {
        var js,
          fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {
          return;
        }
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.com/en_US/messenger.Extensions.js";
        fjs.parentNode.insertBefore(js, fjs);
      })(document, "script", "Messenger");

      window.extAsyncInit = () => {
        // TODO: How to parse env file from here?
        MessengerExtensions.getSupportedFeatures(
          function success(result) {
            let features = result.supported_features;
            if (features.includes("context")) {
              MessengerExtensions.getContext(
                "<APP_ID>",
                function success(thread_context) {
                  // success
                  document.getElementById("psid").value = thread_context.psid;
                },
                function error(err) {
                  // error
                  console.log(err);
                }
              );
            }
          },
          function error(err) {
            // error retrieving supported features
            console.log(err);
          }
        );
        document
          .getElementById("submitButton")
          .addEventListener("click", () => {
            MessengerExtensions.requestCloseBrowser(
              function success() {
                console.log("Webview closing");
              },
              function error(err) {
                console.log(err);
              }
            );
          });
      };
    </script>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script
      src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
      integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
      integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
      integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
