<?php
/**
 * A Symfony 1.4 wrapper to call the Racktables API as provided by https://github.com/permosegaard/racktables
 *
 * @author Robin Corps <robin@ngse.co.uk>
 * @version 0.1
 * @package sfRacktables
 *
 * APT Methods
 * @method array get_status() get status
 * @method array get_8021q() get overall 8021q status
 * @method array add_tag(array('parent_id', 'is_assignable', 'tag_name')) add a tag
 * @method array delete_tag(array('tag_id')) delete a named tag
 * @method array get_taglist() gets user-defined tags as a list
 * @method array get_tagtree() gets user-defined tags as a tree
 * @method array get_ipv4space() get overall IPv4 space
 * @method array get_ipv6space() get overall IPv6 space
 * @method array get_ipv4network(array('network_id')) get a single IPv4 network
 * @method array allocate_ipv4(array('name' 'network' 'domain' 'vlan' 'connected')) allocate an ipv4 network
 * @method array get_vlan_domains() get all VLAN domains
 * @method array get_vlan_domain(array('domain_id')) get VLANs in one domain
 * @method array get_vlan(array('vlan_ck')) get info for one VLAN
 * @method array get_rackspace(array()) get overall rackspace status
 * @method array get_rack(array('rack_id')) get info for a rack
 * @method array get_ipaddress(array('ip')) get info for a single IP address
 * @method array get_object(array('include_attrs' 'key_attrs_on' 'object_id' 'include_unset_attrs')) get one object
 * @method array get_object_allocation(array('object_id')) get the location of an object
 * @method array update_object_allocation(array('object_id' 'allocate_to')) update where an object is installed in rackspace
 * @method array link_entities(array('parent_entity_type' 'parent_entity_id' 'child_entity_type' 'child_entity_id')) link two entities (most often used for server / chassis mounting)
 * @method array add_object(array('object_type_id' 'virtual_objects' 'object_label' 'object_asset_no' 'object_name' 'taglist')) add one object
 * @method array edit_object(array('object_id' 'object_name' 'object_label' 'object_asset_no' 'object_comment', 'attr_*')) edit an existing object
 * @method array update_object_tags(array('taglist' 'object_id')) update user-defined tags for an object
 * @method array snmp_sync_object(array('object_id' 'ver' 'community' 'sec_name' 'sec_level' 'auth_protocol' 'auth_passphrase' 'priv_protocol' 'priv_passphrase')) sync a switch or PDU's ports using SNMP
 * @method array add_object_ip_allocation(array('bond_name' 'bond_type' 'ip' 'object_id')) update an object's IP address
 * @method array delete_object_ip_allocation(array('object_id')) delete an IP address allocation for an object
 * @method array add_port(array('object_id' 'port_name' 'port_type_id' 'port_label' 'port_l2address')) add a port to an object
 * @method array delete_port(array('port_id' 'object_id')) delete a port from an object
 * @method array link_port(array('port' 'remote_port' 'cable')) link a port
 * @method array unlink_port(array('port_id')) unlink a port
 * @method array get_port(array('port_id')) get data on a given port
 * @method array delete_object(array('object_id')) delete an object
 * @method array get_depot(array('include_attrs' 'key_attrs_on')) get all objects
 * @method array get_attributes() get all available object attributes
 * @method array get_dictionary() get all chapters in the dictionary
 * @method array get_chapter(array('chapter_no' 'style')) get dictionary chapter
 * @method array add_chapter_entry(array('chapter_no' 'dict_value')) add en entry to a chapter
 * @method array delete_chapter_entry(array('chapter_no' 'dict_value')) delete an entry from a chapter
 * @method array add_domain(array('domain_description')) add a vlan domain
 * @method array delete_domain(array('domain_id')) remove a vlan domain by id
 * @method array get_domains() get a list of all the vlan domains
 * @method array add_vlan(array('domain_id' 'vlan_id' 'vlan_type' 'vlan_description')) add a vlan to specified domain
 * @method array delete_vlan(array('domain_id' 'vlan_id')) delete a vlan from a specified domain
 * @method array get_vlans() get a list of all vlans within specified domain
 * @method array search(array('term')) perform a generic search
 */
class sfRacktables
{
  /**
   * Store the username
   *
   * @var string
   */
  protected $username;

  /**
   * Store the password
   *
   * @var string
   */
  protected $password;

  /**
   * Store the Racktables API URL
   *
   * @var string
   */
  protected $url;


  /**
   * Create a new sfRacktables object
   *
   * @param string $username
   * @param string $password
   * @param string $url
   */
  public function __construct($username = null, $password = null, $url = null)
  {
    if ($username !== null)
    {
      $this->username = $username;
    }
    else
    {
      $this->username = sfConfig::get('app_racktables_username');
    }

    if ($password !== null)
    {
      $this->password = $password;
    }
    else
    {
      $this->password = sfConfig::get('app_racktables_password');
    }

    if ($url !== null)
    {
      $this->url = $url;
    }
    else
    {
      $this->url = sfConfig::get('app_racktables_url');
    }
  }


  public function __call($method, $arguments = null)
  {
    if ($arguments === null)
    {
      $arguments = array();
    }

    if (!is_array($arguments))
    {
      throw new RacktablesInvalidArgumentsException();
    }

    $arguments['method'] = $method;

    return $this->callAPI($arguments);
  }


  protected function callAPI($params)
  {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $http_result = curl_exec($ch);
    $error       = curl_error($ch);
    $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($http_code != 200) {
      return array(
        'error' => $error
      );
    } else {
      return json_decode($http_result, true);
    }
  }
}

