<mjml>
    <mj-head>
      <mj-title>NewMessages</mj-title>
      <mj-preview>New Messages</mj-preview>
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
              New Messages
            </mj-text>
            <mj-text color="#637381" font-size="16px">
              Hi {{ $dynamicData['name'] }},
            </mj-text>

               <mj-text color="#637381" font-size="16px">
              You have
              <mj-raw>
                  <span style="font-weight:bold">{{ $dynamicData['num'] }}</span> unacknowleged messages:
              </mj-raw>
            </mj-text>

            <mj-text padding-bottom="0">
                <mj-raw>
                  <span style="font-weight:bold">{{ $dynamicData['numApp'] }} Messages regarding your applications:</span>
              </mj-raw>
            </mj-text>
            <mj-table>
                @foreach($dynamicData['appMessages'] as $message)
                <tr>
                    <td>
                        {!! nl2br($message) !!}
                    </td>
                </tr>
                <tr>
                  <td style="padding: 0 0 30px 0;"></td>
                </tr>
                @endforeach
            </mj-table>


            <mj-text padding-bottom="0" padding-top="30px">
                <mj-raw>
                  <span style="font-weight:bold">{{ $dynamicData['numAppRev'] }} Messages regarding applications requiring review:</span>
                </mj-raw>
            </mj-text>
            <mj-table>
                @foreach($dynamicData['appRevMessages'] as $message)
                <tr>
                    <td>
                        {!! nl2br($message) !!}
                    </td>
                </tr>
                <tr>
                  <td style="padding: 0 0 30px 0;"></td>
                </tr>
                @endforeach
            </mj-table>


            <mj-text padding-bottom="0" padding-top="30px">
                <mj-raw>
                    <span style="font-weight:bold">{{ $dynamicData['numOther'] }} Other messages:</span>
                </mj-raw>
            </mj-text>
            <mj-table>
                @foreach($dynamicData['otherMessages'] as $message)
                <tr>
                    <td>
                        {!! nl2br($message) !!}
                    </td>
                </tr>
                <tr>
                  <td style="padding: 0 0 30px 0;"></td>
                </tr>
                @endforeach
            </mj-table>

            <mj-text color="#637381" font-size="16px">
                To acknowledge these messages, please log in to LeaveOnTime by pressing the button below or following the link at the end of this email.
            </mj-text>

            <mj-button background-color="#A9D1DA" color="#000000" font-size="16px" font-weight="bold" href="https://leaveontime.cyber.curtin.io" width="240px" padding-bottom="30px" padding-top="30px">
                View in App
              </mj-button>


              <mj-text color="#212b35" font-size="12px" align="center" text-transform="lowercase" font-weight="bold" padding-top="0px">
                <a class="text-link" href="https://leaveontime.cyber.curtin.io">leaveontime.cyber.curtin.io</a>
              </mj-text>
          </mj-column>
          <mj-column width="90%">
            <mj-text color="#445566" font-size="11px" align="center" line-height="16px">
              You are receiving this email because your organisation is using LeaveOnTime and has created an account for you.
            </mj-text>

          </mj-column>
        </mj-section>
      </mj-wrapper>
    </mj-body>
  </mjml>
