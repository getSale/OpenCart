<?php

/**
 * 2016 GetSale
 * @author    GetSale Team <http://getsale.io/>
 * @copyright 2016 GetSale
 * @license   GNU General Public License, version 3
 */
class ControllerModuleGetsale extends Controller
{
    private $error = array();
    private $name = "GetSale";
    private $ver = '1.0.0';

    public function index()
    {
        $this->load->language('module/getsale');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('getsale', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('module/getsale', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_email'] = $this->language->get('entry_text_email');
        $data['text_key'] = $this->language->get('entry_text_key');
        $data['button_apply'] = $this->language->get('entry_title_apply');
        $data['auth'] = $this->language->get('entry_auth');
        $data['email_placeholder'] = $this->language->get('entry_email_placeholder');
        $data['key_placeholder'] = $this->language->get('entry_key_placeholder');
        $data['tech_support'] = $this->language->get('entry_tech_support');

        $data['succ_mess1'] = $this->language->get('entry_succ_mess1');
        $data['succ_mess2'] = $this->language->get('entry_succ_mess2');

        $data['url'] = HTTP_CATALOG;
        $data['ver'] = $this->ver;
        $data['error_warning'] = '';

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['code'])) {
            $data['error_code'] = $this->error['code'];
        } else {
            $data['error_code'] = '';
        }

        if (isset($this->request->post['getsale_id'])) {
            $data['getsale_id'] = $this->request->post['getsale_id'];
        } else {
            $data['getsale_id'] = $this->config->get('getsale_id');
        }

        if (isset($this->request->post['getsale_code'])) {
            $data['getsale_code'] = $this->request->post['getsale_code'];
        } else {
            $data['getsale_code'] = $this->config->get('getsale_code');
        }

        if (isset($this->request->post['getsale_email'])) {
            $data['getsale_email'] = $this->request->post['getsale_email'];
        } else {
            $data['getsale_email'] = $this->config->get('getsale_email');
        }

        if (isset($this->request->post['getsale_key'])) {
            $data['getsale_key'] = $this->request->post['getsale_key'];
        } else {
            $data['getsale_key'] = $this->config->get('getsale_key');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/getsale', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('module/getsale', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('module/getsale.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/getsale')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->language('module/getsale');

        $domain = 'http://edge.getsale.io';

        if (isset($this->request->post['getsale_key'])) {
            $key = $this->request->post['getsale_key'];
        } else {
            $key = $this->config->get('getsale_key');
        }

        if (isset($this->request->post['getsale_email'])) {
            $email = $this->request->post['getsale_email'];
        } else {
            $email = $this->config->get('getsale_email');
        }

        $url = $this->request->post['url'];
        if (($email == '') OR ($key == '')) {
            $this->error['warning'] = $this->language->get('text_error_empty');
        }

        $ch = curl_init();

        $jsondata = json_encode(array(
                'email' => $email,
                'key' => $key,
                'url' => $url,
                'cms' => 'opencart')
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, $domain . "/api/registration.json");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        $json_result = json_decode($server_output);
        if (isset($json_result->status)) {
            if (($json_result->status == 'OK') && (isset($json_result->payload))) {
                if (isset($json_result->payload->projectId)) {
                    $this->request->post['getsale_id'] = $json_result->payload->projectId;
                    $this->request->post['getsale_code'] = trim(htmlspecialchars($this->intrg_code($json_result->payload->projectId)));
                    $this->request->post['getsale_email'] = $email;
                    $this->request->post['getsale_key'] = $key;
                }
            } elseif ($json_result->status = 'error') {
                $this->error['warning'] = $this->language->get('error_error_'.$json_result->code);
            }
        }
        curl_close($ch);

        return !$this->error;
    }

    public static function intrg_code($project_id)
    {
        return "<!-- GETSALE CODE START -->
		  <script type='text/javascript'>
			(function(d, w, c) {
			  w[c] = {
				projectId: " . $project_id . "
			  };

			  var n = d.getElementsByTagName('script')[0],
			  s = d.createElement('script'),
			  f = function () { n.parentNode.insertBefore(s, n); };
			  s.type = 'text/javascript';
			  s.async = true;
			  s.src = '//rt.edge.getsale.io/loader.js';

			  if (w.opera == '[object Opera]') {
				  d.addEventListener('DOMContentLoaded', f, false);
			  } else { f(); }

			})(document, window, 'getSaleInit');
			console.log('script');
			</script>
		<!-- GETSALE CODE END -->";
    }
}
