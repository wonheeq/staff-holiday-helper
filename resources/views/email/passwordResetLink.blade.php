<mjml>
    <mj-head>
      <mj-title>Password Reset</mj-title>
      <mj-preview>Password Reset</mj-preview>
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
      <mj-wrapper background-url="https://lh3.googleusercontent.com/drive-viewer/AITFw-xjFsV_ZTqdL1QL4Qg_dxM66dcj6y03WQtLSOB4wPs2JWKGxl30_6mmv0wOIBKfvKhG4X1aYSOUPliVhtgHpg9lfrEn1A=s1600" css-class="body-section" padding="0px">
        <mj-section>
            <mj-column>
              <mj-spacer height="10px" ></mj-spacer>
          </mj-column>
        </mj-section>
        <mj-section background-size="cover" padding="0px">
          <mj-column vertical-align="middle" width="80%">
            <mj-image src= "https://drive.google.com/uc?export=download&id=1xaMKV4-Ik-vWjqYbu_6BNpcuRncClomH" padding="0px"></mj-image>
          </mj-column>
          <mj-column vertical-align="middle" width="80%" background-color="#FFFFFF">
            <mj-text color="#212b35" font-weight="bold" font-size="20px" align="center">
              Password Reset
            </mj-text>
            <mj-text color="#637381" font-size="16px">
              Hi {{ $dynamicData['name'] }},
            </mj-text>
               <mj-text color="#637381" font-size="16px">
              You are receiving this email because we received a password reset request for your account. Please click the button bellow to reset your password.
            </mj-text>

            <mj-button background-color="#A9D1DA" color="#000000" font-size="16px" font-weight="bold" href= "{{ $dynamicData['url'] }}" width="210px" padding-bottom="20px" padding-top="20px">
              Reset Password
            </mj-button>
            <mj-text color="#637381" font-size="16px">
              This password reset link will expire in 60 minutes. If the above link does not work, kindly copy and paste the url below into your web browser:
            </mj-text>
            <mj-text color="#637381" font-size="12px" padding-top="0px">
              <a class="text-link" href="{{ $dynamicData['url'] }}">{{ $dynamicData['url'] }}</a>
            </mj-text>
            <mj-text color="#637381" font-size="16px" padding-top="0px">
              If you did not request a password reset, no further action is required.
            </mj-text>
            <mj-text color="#212b35" font-size="12px" align="center" text-transform="lowercase" font-weight="bold">
              <a class="text-link" href="http://localhost:8000">www.LeaveOnTime.com.au</a>
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
