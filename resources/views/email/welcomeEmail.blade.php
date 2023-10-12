<mjml>
  <mj-head>
    <mj-title>Welcome Email</mj-title>
    <mj-attributes>
      <mj-all font-family="'Helvetica Neue', Helvetica, Arial, sans-serif"></mj-all>
      <mj-text font-weight="400" font-size="16px" color="#000000" line-height="24px" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif"></mj-text>
    </mj-attributes>
    <mj-style inline="inline">
      .body-section {
      -webkit-box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
      -moz-box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
      box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
      }
    </mj-style>
    <mj-style inline="inline">
      .text-link {
      color: #5e6ebf
      }
    </mj-style>
    <mj-style inline="inline">
      .footer-link {
      color: #888888
      }
    </mj-style>
    <mjml>
    </mjml>
  </mj-head>
  <mj-body width="600px">
    <mj-wrapper background-url="https://drive.google.com/uc?export=download&id=1vCe-ypjgifrHt5rZWgyRkZSP1WXfQJed" css-class="body-section" padding="0px">
      <mj-section>
          <mj-column>
            <mj-spacer height="10px" ></mj-spacer>
        </mj-column>
      </mj-section>
      <mj-section background-size="cover" padding="0px">
        <mj-column vertical-align="middle" width="80%">
          <mj-image src= "https://drive.google.com/uc?export=download&id=1cnW2gDgRpv4o1uzUI5HFGXxN3D2y-EIi" padding="0px"></mj-image>
        </mj-column>
        <mj-column vertical-align="middle" width="80%" background-color="#FFFFFF">
          <mj-text color="#212b35" font-weight="bold" font-size="20px" align="center">
            Welcome to LeaveOnTime
          </mj-text>
          <mj-text color="#637381" font-size="16px">
            Hi {{ $dynamicData['name'] }},
          </mj-text>
 	        <mj-text color="#637381" font-size="16px">
            Welcome to LeaveOnTime, the leave booking system designed to help you book your time-off and leave on time.
          </mj-text>
          <mj-text color="#637381" font-size="16px" font-weight="600">
          	Here are your account details:
          </mj-text>
          <mj-text>
          	Account No: {{ $dynamicData['accountNo'] }}
          </mj-text>

          <mj-text color="#637381" font-size="16px">
            To finish setting up and create your password, please log in to LeaveOnTime with the details provided by pressing the button below or following the link at the end of this email.
          </mj-text>

          <mj-button background-color="#A9D1DA" color="#000000" font-size="16px" font-weight="bold" href="{{ $dynamicData['link'] }}" width="240px" padding-bottom="30px" padding-top="30px">
                Go To App
          </mj-button>

          <mj-text color="#212b35" font-size="12px" align="center" text-transform="lowercase" font-weight="bold">
            <a class="text-link" href="{{ $dynamicData['link'] }}">leaveontime.cyber.curtin.io</a>
          </mj-text>
          
        </mj-column>
        <mj-column width="90%">
          <mj-text color="#445566" font-size="11px" align="center" line-height="16px">
            You are receiving this email because your organisation is using LeaveOnTime and has created an account for you.
          </mj-text>
          <mj-text color="#445566" font-size="11px" align="center" line-height="16px">
            &copy; Leave On Time.
          </mj-text>
        </mj-column>
      </mj-section>
    </mj-wrapper>
  </mj-body>
</mjml>
