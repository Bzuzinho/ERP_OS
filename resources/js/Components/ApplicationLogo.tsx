import { ImgHTMLAttributes } from 'react';

type ApplicationLogoProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'>;

export default function ApplicationLogo({ className = '', ...props }: ApplicationLogoProps) {
    return (
        <img
            {...props}
            src="/branding/logo.png"
            alt="JuntaOS"
            className={`object-contain ${className}`.trim()}
        />
    );
}
