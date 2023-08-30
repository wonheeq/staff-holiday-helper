<mjml>
  <mj-head>
    <mj-title>Booking Cancelled</mj-title>
    <mj-preview>Booking Cancelled</mj-preview>
    <mj-attributes>
      <mj-all font-family="'Helvetica Neue', Helvetica, Arial, sans-serif"></mj-all>
      <mj-text font-weight="400" font-size="16px" color="#000000" line-height="24px" font-family="'Helvetica Neue', Helvetica, Arial, sans-serif"></mj-text>
    	
    </mj-attributes>
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
            Booking Cancelled
          </mj-text>
          <mj-text color="#637381" font-size="16px">
            Hi {{ $dynamicData['name'] }},
          </mj-text>
          
          
 	        <mj-text color="#637381" font-size="16px">
            The following bookings you made have been  
            <mj-raw>
            	<span style="font-weight:bold">cancelled:</span>
            </mj-raw>
          </mj-text>
          
          <mj-text padding-bottom="0px">
          	Application No.: {{ $dynamicData['appNo'] }}
          </mj-text>
          <mj-text padding-top="0px" padding-bottom="0px" >
          	Nominee: {{ $dynamicData['nName'] }}
          </mj-text>
          <mj-text padding-top="0" padding-bottom="0px">
          	Role: {{ $dynamicData['role'] }}
          </mj-text>
          <mj-text padding-top="0" padding-bottom="0px" >
          	Unit Code: {{ $dynamicData['uCode'] }}
          </mj-text>
          <mj-text padding-top="0" padding-bottom="0px">
          	Unit Name: {{ $dynamicData['uName'] }}
          </mj-text>
          <mj-text padding-top="0" padding-bottom="0px">
          	Period: {{ $dynamicData['period'] }}
          </mj-text>

          <mj-text padding-top="30px" color="#212b35" font-size="12px" align="center" text-transform="lowercase" font-weight="bold">
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