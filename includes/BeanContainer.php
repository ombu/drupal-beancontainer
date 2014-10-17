<?php

/**
 * @file
 * Bean plugin object for bean container.
 */

class BeanContainer extends BeanPlugin {
  /**
   * @see: parent::values().
   */
  public function values() {
    $values = parent::values();

    $values['children'] = array();
    $values['display_type'] = 'simple';

    return $values;
  }

  /**
   * @see: parent::form().
   */
  public function form($bean, $form, &$form_state) {
    $form = parent::form($bean, $form, $form_state);

    $form['children'] = array(
      '#type' => 'value',
      '#value' => $bean->children,
    );

    foreach (bean_container_get_style() as $k => $v) {
      $styles[$k] = $v['label'];
    }

    $form['display_type'] = array(
      '#type' => 'select',
      '#title' => t('Display type'),
      '#options' => $styles,
      '#default_value' => $bean->display_type,
    );

    return $form;
  }

  /**
   * Return the block content.
   *
   * @param $bean
   *   The bean object being viewed.
   * @param $content
   *   The default content array created by Entity API.  This will include any
   *   fields attached to the entity.
   * @param $view_mode
   *   The view mode passed into $entity->view().
   * @return
   *   Return a renderable content array.
   */
  public function view($bean, $content, $view_mode = 'default', $langcode = NULL) {
    $children = array();

    if (!empty($bean->children)) {
      $i = 1;
      foreach ($bean->children as $child) {
        $child_bean = bean_load($child);
        if ($child_bean && bean_access('view', $child_bean)) {
          $children[] = $child_bean;
        }
      }

      $style = bean_container_get_style($bean->display_type);
      $content['bean'][$bean->delta]['children'] = array(
        '#theme' => $style['theme_function'],
        '#children' => $children,
        '#display_type' => $bean->display_type,
        '#parent' => $bean,
      );
    }
    elseif (user_access('edit any bean_container bean')) {
      $content['bean'][$bean->delta]['empty'] = array(
        '#markup' => t('This is an empty block container. You can add blocks to it by clicking <a href="!url">"Manage Children"</a> on the container cog menu', array(
          '!url' => $bean->url() . '/manage-children',
        )),
      );
    }

    return $content;
  }
}
