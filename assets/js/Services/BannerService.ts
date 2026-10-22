import LoaderBannerService from '@wexample/symfony-loader/js/Services/BannerService';

/**
 * The loader's banner service, wearing this design system's banner.
 *
 * Announcing something is the loader's behaviour; the capsule it is announced
 * in is ours. An application registers this one rather than the base — the
 * loader lets a subclass take the place of the service registered under the
 * same name — and the base stays usable by an application shipping its own
 * markup.
 */
export default class BannerService extends LoaderBannerService {
  public static componentPath: string = '@WexampleSymfonyDesignSystemBundle/components/banner';
}
